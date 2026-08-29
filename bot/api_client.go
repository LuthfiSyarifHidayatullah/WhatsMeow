package main

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"mime/multipart"
	"net/http"
	"time"
)

type APIClient struct {
	baseURL    string
	token      string
	httpClient *http.Client
}

type IncomingMessageRequest struct {
	Sender  string `json:"sender"`
	ChatJID string `json:"chat_jid"`
	Text    string `json:"text"`
}

type MessageResponse struct {
	Reply       string `json:"reply"`
	Action      string `json:"action"`       // "bot_reply", "escalate", "transfer"
	ServiceID   int    `json:"service_id"`   // ID layanan jika di-escalate
	OfficerID   int    `json:"officer_id"`   // ID petugas yang ditugaskan
	SessionID   string `json:"session_id"`   // ID sesi chat
}

func NewAPIClient(baseURL, token string) *APIClient {
	return &APIClient{
		baseURL: baseURL,
		token:   token,
		httpClient: &http.Client{
			Timeout: 30 * time.Second,
		},
	}
}

func (c *APIClient) ProcessIncomingMessage(sender, chatJID, text string) (*MessageResponse, error) {
	payload := IncomingMessageRequest{
		Sender:  sender,
		ChatJID: chatJID,
		Text:    text,
	}

	jsonData, err := json.Marshal(payload)
	if err != nil {
		return nil, fmt.Errorf("failed to marshal request: %w", err)
	}

	req, err := http.NewRequest("POST", c.baseURL+"/bot/incoming", bytes.NewBuffer(jsonData))
	if err != nil {
		return nil, fmt.Errorf("failed to create request: %w", err)
	}

	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Authorization", "Bearer "+c.token)

	resp, err := c.httpClient.Do(req)
	if err != nil {
		return nil, fmt.Errorf("failed to send request: %w", err)
	}
	defer resp.Body.Close()

	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, fmt.Errorf("failed to read response: %w", err)
	}

	if resp.StatusCode != http.StatusOK {
		return nil, fmt.Errorf("API returned status %d: %s", resp.StatusCode, string(body))
	}

	var msgResp MessageResponse
	if err := json.Unmarshal(body, &msgResp); err != nil {
		return nil, fmt.Errorf("failed to unmarshal response: %w", err)
	}

	return &msgResp, nil
}

// ProcessIncomingMedia uploads media file to Laravel and processes it
func (c *APIClient) ProcessIncomingMedia(sender, chatJID, mediaType, caption, fileName, mimeType string, data []byte) (*MessageResponse, error) {
	var buf bytes.Buffer
	writer := multipart.NewWriter(&buf)

	// Add text fields
	_ = writer.WriteField("sender", sender)
	_ = writer.WriteField("chat_jid", chatJID)
	_ = writer.WriteField("media_type", mediaType)
	_ = writer.WriteField("caption", caption)
	_ = writer.WriteField("mime_type", mimeType)

	// Add file
	part, err := writer.CreateFormFile("file", fileName)
	if err != nil {
		return nil, fmt.Errorf("failed to create form file: %w", err)
	}
	if _, err := part.Write(data); err != nil {
		return nil, fmt.Errorf("failed to write file data: %w", err)
	}

	if err := writer.Close(); err != nil {
		return nil, fmt.Errorf("failed to close writer: %w", err)
	}

	req, err := http.NewRequest("POST", c.baseURL+"/bot/incoming-media", &buf)
	if err != nil {
		return nil, fmt.Errorf("failed to create request: %w", err)
	}

	req.Header.Set("Content-Type", writer.FormDataContentType())
	req.Header.Set("Authorization", "Bearer "+c.token)

	resp, err := c.httpClient.Do(req)
	if err != nil {
		return nil, fmt.Errorf("failed to send request: %w", err)
	}
	defer resp.Body.Close()

	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, fmt.Errorf("failed to read response: %w", err)
	}

	if resp.StatusCode != http.StatusOK {
		return nil, fmt.Errorf("API returned status %d: %s", resp.StatusCode, string(body))
	}

	var msgResp MessageResponse
	if err := json.Unmarshal(body, &msgResp); err != nil {
		return nil, fmt.Errorf("failed to unmarshal response: %w", err)
	}

	return &msgResp, nil
}

// SendMessageToUser sends a message from officer (live chat) back to WhatsApp user
func (c *APIClient) NotifyMessageSent(sessionID, message string) error {
	payload := map[string]string{
		"session_id": sessionID,
		"message":    message,
		"status":     "sent",
	}

	jsonData, err := json.Marshal(payload)
	if err != nil {
		return err
	}

	req, err := http.NewRequest("POST", c.baseURL+"/bot/message-status", bytes.NewBuffer(jsonData))
	if err != nil {
		return err
	}

	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Authorization", "Bearer "+c.token)

	resp, err := c.httpClient.Do(req)
	if err != nil {
		return err
	}
	defer resp.Body.Close()

	return nil
}
