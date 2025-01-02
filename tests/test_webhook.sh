#!/bin/bash

# Lê o conteúdo do arquivo JSON
json_data=$(cat "$(dirname "$0")/shopify_order_test.json")

# Lê a chave do webhook do arquivo .env
webhook_secret=$(grep SHOPIFY_WEBHOOK_SECRET "$(dirname "$0")/../.env" | cut -d '=' -f2)

# Gera o HMAC-SHA256
hmac=$(echo -n "$json_data" | openssl dgst -sha256 -hmac "$webhook_secret" -binary | base64)

# Faz a requisição POST
curl -X POST embaixadores.test/shopify/orders/new \
  -H "Content-Type: application/json" \
  -H "X-Shopify-Hmac-Sha256: $hmac" \
  -d "$json_data"

echo -e "\n\nHMAC gerado: $hmac"
