# Real-time Kanban Board Setup

This document explains how to set up and run the real-time Kanban board with WebSocket support.

## Prerequisites

- PHP 8.1+
- Node.js 18+
- Composer
- NPM

## Setup Steps

### 1. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 2. Environment Configuration

The `.env` file has been configured with the following Reverb settings:

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=local
REVERB_APP_KEY=local
REVERB_APP_SECRET=local
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 3. Database Setup

```bash
# Run migrations
php artisan migrate

# Optional: Seed with sample data
php artisan db:seed
```

### 4. Build Frontend Assets

```bash
npm run build
```

### 5. Running the Application

You need to run **three separate processes** for full real-time functionality:

#### Terminal 1: Laravel Application
```bash
php artisan serve
```
This will start the Laravel app on `http://localhost:8000`

#### Terminal 2: Reverb WebSocket Server
```bash
php artisan reverb:start
```
This will start the WebSocket server on `http://localhost:8080`

#### Terminal 3: Queue Worker (for broadcasting)
```bash
php artisan queue:work
```
This processes the broadcast events in the background

## Testing Real-time Features

1. **Open two browser windows/tabs** to the same board
2. **Log in as different users** (or use incognito mode for the second window)
3. **Perform actions** in one window:
   - Create a new card
   - Move a card between columns
   - Edit a card's title or description
   - Delete a card
4. **Observe** that changes appear instantly in the other window

## Features Implemented

✅ **Real-time card creation** - New cards appear instantly for all users
✅ **Real-time card movement** - Card drag & drop updates in real-time
✅ **Real-time card editing** - Title and description changes sync instantly
✅ **Real-time card deletion** - Deleted cards disappear for all users
✅ **Private channels** - Only users with board access receive updates
✅ **WebSocket authentication** - Secure channel access

## Troubleshooting

### WebSocket Connection Issues
- Ensure Reverb server is running on port 8080
- Check that no firewall is blocking the connection
- Verify the `.env` configuration matches the Reverb server settings

### Events Not Broadcasting
- Ensure the queue worker is running (`php artisan queue:work`)
- Check Laravel logs for any broadcasting errors
- Verify the `BROADCAST_CONNECTION=reverb` setting

### Frontend Not Updating
- Check browser console for WebSocket connection errors
- Ensure the frontend assets are built (`npm run build`)
- Verify the Vite environment variables are set correctly

## Architecture

The real-time system uses:
- **Laravel Reverb** - WebSocket server for real-time communication
- **Laravel Broadcasting** - Event broadcasting system
- **Laravel Echo** - Frontend WebSocket client
- **Private Channels** - Secure board-specific channels (`board.{id}`)
- **Queue System** - Background processing of broadcast events

## Security

- All channels are private and require authentication
- Users can only access boards they have permission to view
- Broadcasting events are queued and processed securely
