package main

import (
	"context"
	"fmt"
	"os"
	"os/signal"
	"syscall"

	_ "github.com/mattn/go-sqlite3"
	"github.com/mdp/qrterminal/v3"
	"go.mau.fi/whatsmeow"
	"go.mau.fi/whatsmeow/proto/waE2E"
	"go.mau.fi/whatsmeow/store/sqlstore"
	"go.mau.fi/whatsmeow/types/events"
	waLog "go.mau.fi/whatsmeow/util/log"
	"google.golang.org/protobuf/proto"
)

var client *whatsmeow.Client
var apiClient *APIClient

func main() {
	// Load config
	config := LoadConfig()

	// Initialize API client
	apiClient = NewAPIClient(config.APIBaseURL, config.APIToken)

	// Setup WhatsApp store
	dbLog := waLog.Stdout("Database", "WARN", true)
	container, err := sqlstore.New("sqlite3", "file:whatsapp.db?_journal_mode=WAL&_foreign_keys=on", dbLog)
	if err != nil {
		panic(err)
	}

	deviceStore, err := container.GetFirstDevice()
	if err != nil {
		panic(err)
	}

	clientLog := waLog.Stdout("Client", "INFO", true)
	client = whatsmeow.NewClient(deviceStore, clientLog)
	client.AddEventHandler(eventHandler)

	if client.Store.ID == nil {
		// Login with QR code
		qrChan, _ := client.GetQRChannel(context.Background())
		err = client.Connect()
		if err != nil {
			panic(err)
		}
		for evt := range qrChan {
			if evt.Event == "code" {
				qrterminal.GenerateHalfBlock(evt.Code, qrterminal.L, os.Stdout)
				fmt.Println("Scan QR code di atas dengan WhatsApp")
			} else {
				fmt.Println("Login event:", evt.Event)
			}
		}
	} else {
		err = client.Connect()
		if err != nil {
			panic(err)
		}
	}

	fmt.Println("Bot MPP Kab. Bengkayang sudah aktif!")

	// Start webhook server for receiving messages from Laravel (officer replies)
	webhookPort := os.Getenv("WEBHOOK_PORT")
	if webhookPort == "" {
		webhookPort = "8080"
	}
	webhookServer := NewWebhookServer(webhookPort)
	webhookServer.Start()

	// Wait for exit signal
	c := make(chan os.Signal, 1)
	signal.Notify(c, os.Interrupt, syscall.SIGTERM)
	<-c

	client.Disconnect()
}

func eventHandler(evt interface{}) {
	switch v := evt.(type) {
	case *events.Message:
		handleMessage(v)
	}
}

func handleMessage(msg *events.Message) {
	// Get text from message
	text := ""
	if msg.Message.GetConversation() != "" {
		text = msg.Message.GetConversation()
	} else if msg.Message.GetExtendedTextMessage() != nil {
		text = msg.Message.GetExtendedTextMessage().GetText()
	}

	if text == "" {
		return
	}

	sender := msg.Info.Sender.String()
	chatJID := msg.Info.Chat.String()

	fmt.Printf("[MSG] From: %s | Text: %s\n", sender, text)

	// Process message through chatbot logic
	response := processMessage(sender, chatJID, text)

	if response != "" {
		sendMessage(msg, response)
	}
}

func processMessage(sender, chatJID, text string) string {
	// Send message to Laravel API for processing
	resp, err := apiClient.ProcessIncomingMessage(sender, chatJID, text)
	if err != nil {
		fmt.Printf("[ERROR] Failed to process message: %v\n", err)
		return "Maaf, terjadi kesalahan sistem. Silakan coba lagi nanti."
	}

	return resp.Reply
}

func sendMessage(msg *events.Message, text string) {
	_, err := client.SendMessage(context.Background(), msg.Info.Chat, &waE2E.Message{
		Conversation: proto.String(text),
	})
	if err != nil {
		fmt.Printf("[ERROR] Failed to send message: %v\n", err)
	}
}
