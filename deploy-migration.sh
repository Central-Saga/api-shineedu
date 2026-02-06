#!/bin/bash

# Deploy API to Production and Run Migration (cPanel Shared Hosting)
# This script will:
# 1. SSH to production server
# 2. Pull latest code from dev branch
# 3. Run the new migration to make no_hp nullable
# 4. Clear cache

echo "🚀 Deploying to production (cPanel)..."

# SSH details
SSH_HOST="shineedu@103.163.138.211"
SSH_PORT="45022"
SSH_KEY="~/.ssh/shineedu_id_rsa"
APP_DIR="~/api.shineeducationbali.com"

# Connect to server and execute commands
ssh -p $SSH_PORT -i $SSH_KEY -o IdentitiesOnly=yes $SSH_HOST << 'ENDSSH'
cd ~/api.shineeducationbali.com

echo "📥 Pulling latest code..."
git pull origin dev

echo "🔄 Running migrations..."
php artisan migrate --force

echo "🔄 Clearing cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo "✅ Deployment complete!"
ENDSSH

echo "✅ Done! Migration has been applied to production."
