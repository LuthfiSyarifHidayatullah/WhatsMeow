package main

import "os"

type Config struct {
	APIBaseURL string
	APIToken   string
}

func LoadConfig() *Config {
	apiURL := os.Getenv("API_BASE_URL")
	if apiURL == "" {
		apiURL = "http://localhost:8000/api"
	}

	apiToken := os.Getenv("API_TOKEN")
	if apiToken == "" {
		apiToken = "default-bot-secret-token"
	}

	return &Config{
		APIBaseURL: apiURL,
		APIToken:   apiToken,
	}
}
