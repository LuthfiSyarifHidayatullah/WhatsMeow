package main

import (
	"bufio"
	"os"
	"strings"
)

type Config struct {
	APIBaseURL string
	APIToken   string
}

func LoadConfig() *Config {
	// Load .env file if exists
	loadEnvFile(".env")

	apiURL := os.Getenv("API_BASE_URL")
	if apiURL == "" {
		apiURL = "http://localhost:8000/api"
	}

	apiToken := os.Getenv("API_TOKEN")
	if apiToken == "" {
		apiToken = "mpp-bot-secret-token-2024"
	}

	return &Config{
		APIBaseURL: apiURL,
		APIToken:   apiToken,
	}
}

// loadEnvFile reads a .env file and sets environment variables
func loadEnvFile(filename string) {
	file, err := os.Open(filename)
	if err != nil {
		return // file doesn't exist, skip
	}
	defer file.Close()

	scanner := bufio.NewScanner(file)
	for scanner.Scan() {
		line := strings.TrimSpace(scanner.Text())

		// Skip empty lines and comments
		if line == "" || strings.HasPrefix(line, "#") {
			continue
		}

		// Split on first =
		parts := strings.SplitN(line, "=", 2)
		if len(parts) != 2 {
			continue
		}

		key := strings.TrimSpace(parts[0])
		value := strings.TrimSpace(parts[1])

		// Remove surrounding quotes if present
		if len(value) >= 2 && ((value[0] == '"' && value[len(value)-1] == '"') || (value[0] == '\'' && value[len(value)-1] == '\'')) {
			value = value[1 : len(value)-1]
		}

		// Only set if not already set (env vars take priority)
		if os.Getenv(key) == "" {
			os.Setenv(key, value)
		}
	}
}
