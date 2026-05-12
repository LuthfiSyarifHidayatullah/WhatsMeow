package main

import (
	"context"
	"encoding/json"
	"fmt"
	"net/http"

	"go.mau.fi/whatsmeow/proto/waE2E"
	"go.mau.fi/whatsmeow/types"
	"google.golang.org/protobuf/proto"
)

// WebhookServer listens for HTTP requests from Laravel backend
// to send messages back to WhatsApp users (from officer live chat)
type WebhookServer struct {
	port string
}

type SendMessageRequest struct {
	ChatJID string `json:"chat_jid"`
	Message string `json:"message"`
}

type WebhookResponse struct {
	Success bool   `json:"success"`
	Message string `json:"message"`
}

func NewWebhookServer(port string) *WebhookServer {
	if port == "" {
		port = "8080"
	}
	return &WebhookServer{port: port}
}

func (ws *WebhookServer) Start() {
	mux := http.NewServeMux()
	mux.HandleFunc("/send", ws.handleSendMessage)
	mux.HandleFunc("/health", ws.handleHealth)

	fmt.Printf("[WEBHOOK] Server listening on port %s\n", ws.port)
	go func() {
		if err := http.ListenAndServe(":"+ws.port, mux); err != nil {
			fmt.Printf("[WEBHOOK] Server error: %v\n", err)
		}
	}()
}

func (ws *WebhookServer) handleHealth(w http.ResponseWriter, r *http.Request) {
	json.NewEncoder(w).Encode(WebhookResponse{Success: true, Message: "Bot is running"})
}

func (ws *WebhookServer) handleSendMessage(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	// Verify bot token
	token := r.Header.Get("Authorization")
	config := LoadConfig()
	expectedToken := "Bearer " + config.APIToken
	if token != expectedToken {
		http.Error(w, "Unauthorized", http.StatusUnauthorized)
		return
	}

	var req SendMessageRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "Invalid request body", http.StatusBadRequest)
		return
	}

	if req.ChatJID == "" || req.Message == "" {
		http.Error(w, "chat_jid and message are required", http.StatusBadRequest)
		return
	}

	// Parse JID and send message via WhatsApp
	jid, err := types.ParseJID(req.ChatJID)
	if err != nil {
		json.NewEncoder(w).Encode(WebhookResponse{Success: false, Message: "Invalid JID: " + err.Error()})
		return
	}

	_, err = client.SendMessage(context.Background(), jid, &waE2E.Message{
		Conversation: proto.String(req.Message),
	})

	if err != nil {
		json.NewEncoder(w).Encode(WebhookResponse{Success: false, Message: "Failed to send: " + err.Error()})
		return
	}

	json.NewEncoder(w).Encode(WebhookResponse{Success: true, Message: "Message sent"})
}
