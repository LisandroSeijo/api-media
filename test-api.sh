#!/bin/bash

echo "🧪 Script de prueba del API REST"
echo ""

BASE_URL="http://localhost:8000/api/v1"

# Colores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Health Check
echo -e "${YELLOW}1. Testing Health Check...${NC}"
response=$(curl -s -X GET "$BASE_URL/health")
echo "$response" | jq '.'
echo ""

# 2. Register
echo -e "${YELLOW}2. Testing Register...${NC}"
RANDOM_EMAIL="test$(date +%s)@example.com"
register_response=$(curl -s -X POST "$BASE_URL/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{
    \"name\": \"Test User\",
    \"email\": \"$RANDOM_EMAIL\",
    \"password\": \"password123\",
    \"password_confirmation\": \"password123\"
  }")

echo "$register_response" | jq '.'

# Extraer token
TOKEN=$(echo "$register_response" | jq -r '.data.access_token')
echo -e "${GREEN}Token obtenido: ${TOKEN:0:50}...${NC}"
echo ""

# 3. Get User
echo -e "${YELLOW}3. Testing Get User...${NC}"
curl -s -X GET "$BASE_URL/user" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.'
echo ""

# 4. Get All Posts
echo -e "${YELLOW}4. Testing Get All Posts...${NC}"
curl -s -X GET "$BASE_URL/posts" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.'
echo ""

# 5. Create Post
echo -e "${YELLOW}5. Testing Create Post...${NC}"
create_post_response=$(curl -s -X POST "$BASE_URL/posts" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Test Post",
    "content": "This is a test post created by the test script"
  }')

echo "$create_post_response" | jq '.'

# Extraer ID del post
POST_ID=$(echo "$create_post_response" | jq -r '.data.id')
echo -e "${GREEN}Post creado con ID: $POST_ID${NC}"
echo ""

# 6. Get Post by ID
echo -e "${YELLOW}6. Testing Get Post by ID...${NC}"
curl -s -X GET "$BASE_URL/posts/$POST_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.'
echo ""

# 7. Update Post
echo -e "${YELLOW}7. Testing Update Post...${NC}"
curl -s -X PUT "$BASE_URL/posts/$POST_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Updated Test Post",
    "content": "This post has been updated"
  }' | jq '.'
echo ""

# 8. Delete Post
echo -e "${YELLOW}8. Testing Delete Post...${NC}"
curl -s -X DELETE "$BASE_URL/posts/$POST_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.'
echo ""

# 9. Logout
echo -e "${YELLOW}9. Testing Logout...${NC}"
curl -s -X POST "$BASE_URL/logout" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.'
echo ""

# 10. Try to access protected route after logout (should fail)
echo -e "${YELLOW}10. Testing Access After Logout (should fail)...${NC}"
curl -s -X GET "$BASE_URL/user" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.'
echo ""

echo -e "${GREEN}✅ Todas las pruebas completadas!${NC}"
echo ""
echo "📊 Resumen:"
echo "  - Email de prueba: $RANDOM_EMAIL"
echo "  - Token usado: ${TOKEN:0:30}..."
echo "  - Post ID creado: $POST_ID"
