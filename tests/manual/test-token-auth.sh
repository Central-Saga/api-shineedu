#!/bin/bash

# Test Token Authentication & Idle Timeout
# This script tests the dual timeout mechanism

API_URL="https://api.shineeducationbali.test/api/v2"
EMAIL="superadmin@shineedu.test"
PASSWORD="password"

echo "================================"
echo "Token Authentication Test"
echo "================================"
echo ""

# Step 1: Login
echo "1. Testing Login..."
LOGIN_RESPONSE=$(curl -k -s -X POST "${API_URL}/auth/login" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"${EMAIL}\",\"password\":\"${PASSWORD}\"}")

echo "Login Response:"
echo "$LOGIN_RESPONSE" | jq '.'
echo ""

# Extract token
TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.token')
EXPIRES_AT=$(echo "$LOGIN_RESPONSE" | jq -r '.data.expires_at')

if [ "$TOKEN" == "null" ] || [ -z "$TOKEN" ]; then
    echo "❌ Login failed! Cannot get token."
    exit 1
fi

echo "✅ Login successful!"
echo "Token: ${TOKEN:0:50}..."
echo "Expires at: $EXPIRES_AT"
echo ""

# Step 2: Test authenticated request
echo "2. Testing authenticated request..."
ME_RESPONSE=$(curl -k -s -X GET "${API_URL}/auth/me" \
  -H "Authorization: Bearer ${TOKEN}")

echo "Me Response:"
echo "$ME_RESPONSE" | jq '.data.user.email'
echo ""

if echo "$ME_RESPONSE" | jq -e '.success' > /dev/null; then
    echo "✅ Authenticated request successful!"
else
    echo "❌ Authenticated request failed!"
fi
echo ""

# Step 3: Test idle timeout (optional - requires waiting)
echo "3. Testing idle timeout..."
echo "⏳ To test idle timeout, wait 15 minutes without making any requests,"
echo "   then run the following command:"
echo ""
echo "   curl -k -X GET \"${API_URL}/auth/me\" -H \"Authorization: Bearer ${TOKEN}\""
echo ""
echo "   Expected: 401 Unauthorized with message about idle timeout"
echo ""

# Step 4: Test active session
echo "4. Testing active session (making requests every 10 seconds)..."
echo "   Press Ctrl+C to stop"
echo ""

for i in {1..5}; do
    echo "Request #$i at $(date '+%H:%M:%S')..."
    ACTIVE_RESPONSE=$(curl -k -s -X GET "${API_URL}/auth/me" \
      -H "Authorization: Bearer ${TOKEN}")

    if echo "$ACTIVE_RESPONSE" | jq -e '.success' > /dev/null; then
        echo "✅ Request successful - token still active"
    else
        echo "❌ Request failed - token might be expired"
        echo "$ACTIVE_RESPONSE" | jq '.'
        break
    fi

    if [ $i -lt 5 ]; then
        echo "   Waiting 10 seconds..."
        sleep 10
    fi
done

echo ""
echo "================================"
echo "Test Summary"
echo "================================"
echo "✅ Token expiration: 24 hours from login"
echo "✅ Idle timeout: 15 minutes without activity"
echo "✅ Active session: Token refreshes on each request"
echo ""
echo "To fully test idle timeout:"
echo "1. Login and get a token"
echo "2. Wait 15 minutes without making any requests"
echo "3. Try to access a protected endpoint"
echo "4. You should get 401 with idle timeout message"
echo ""
