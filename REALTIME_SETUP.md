# Real-time Messaging Setup Guide

## Issues Found and Fixed

### 1. Reverb Server Not Running
**Error**: `cURL error 1: Received HTTP/0.9 when not allowed`

This means the Reverb WebSocket server is not running. Broadcasting is trying to send events but can't connect.

### 2. Vue Lifecycle Issues
**Error**: `onMounted is called when there is no active component instance`

Fixed by using Echo instance directly instead of the `useEcho` composable wrapper.

## Required Services

For real-time messaging to work, you need **THREE** terminal windows running:

### Terminal 1: Laravel Development Server
```bash
php artisan serve
# or if using Laravel Herd/Valet, this is already running
```

### Terminal 2: Reverb WebSocket Server
```bash
php artisan reverb:start
```

You should see output like:
```
  INFO  Reverb server started.

  Local: 127.0.0.1:8080
```

### Terminal 3: Queue Worker (for Broadcasting)
```bash
php artisan queue:work
```

This processes broadcast jobs. You should see:
```
  INFO  Processing jobs from the [default] queue.
```

### Terminal 4: Frontend Build (Optional)
If you're actively developing:
```bash
npm run dev
```

Or build once:
```bash
npm run build
```

## Testing Real-time Messaging

### Step 1: Verify Services Are Running
1. Check Reverb is running (Terminal 2 should show "Reverb server started")
2. Check Queue worker is running (Terminal 3 should show "Processing jobs")

### Step 2: Open Two Browser Windows
1. Window 1: Login as User A
2. Window 2: Login as User B (or use incognito mode)

### Step 3: Start a Conversation
1. In Window 1 (User A):
   - Go to Inbox
   - Click on a conversation with User B
   - Chat drawer opens

2. In Window 2 (User B):
   - Go to Inbox
   - Click on the same conversation
   - Chat drawer opens

### Step 4: Send Messages
1. In Window 1 (User A):
   - Type a message and send
   - You should see it appear immediately in your chat

2. In Window 2 (User B):
   - **WITHOUT REFRESHING**, you should see the message appear
   - Check browser console (F12) for:
     ```
     Setting up Echo listener for conversation: X
     Echo listener subscribed successfully
     New message received via Echo: {message: {...}}
     ```

### Step 5: Verify in Console
Open browser console (F12 > Console tab) in both windows. You should see:
```javascript
Setting up Echo listener for conversation: 1
Echo listener subscribed successfully
```

When a message is sent, the receiving window should show:
```javascript
New message received via Echo: {message: {...}}
```

## Troubleshooting

### Issue: "Echo instance not available"
**Solution**: Make sure you ran `npm run build` or `npm run dev` after updating the code.

### Issue: Messages don't appear in real-time
**Check**:
1. Is Reverb running? (`php artisan reverb:start`)
2. Is Queue worker running? (`php artisan queue:work`)
3. Check `.env` has `BROADCAST_CONNECTION=reverb`
4. Check browser console for errors

### Issue: "Failed to connect to Reverb"
**Check**:
1. `.env` file has correct Reverb configuration:
   ```env
   REVERB_HOST=almithaq.test
   REVERB_PORT=8080
   REVERB_SCHEME=https
   ```
2. If using localhost:
   ```env
   REVERB_HOST=localhost
   REVERB_PORT=8080
   REVERB_SCHEME=http
   ```

### Issue: Queue jobs not processing
**Solution**:
```bash
# Stop the queue worker (Ctrl+C)
# Clear failed jobs
php artisan queue:clear
# Restart
php artisan queue:work
```

### Issue: Channel authorization fails
**Check**:
```bash
# Check if route is registered
php artisan route:list --path=broadcasting
```

## Development vs Production

### Development (Current Setup)
- Reverb runs locally
- Queue processed synchronously or via `queue:work`

### Production
- Use queue driver: `QUEUE_CONNECTION=redis` or `QUEUE_CONNECTION=database`
- Use supervisor to keep `queue:work` and `reverb:start` running
- Consider using Laravel Forge or similar for deployment

## Console Commands Reference

```bash
# Start Reverb WebSocket Server
php artisan reverb:start

# Start Queue Worker
php artisan queue:work

# View Queue Stats
php artisan queue:monitor

# Clear Failed Jobs
php artisan queue:flush

# Check Routes
php artisan route:list --path=broadcasting

# Test Broadcasting
php artisan tinker
>>> broadcast(new App\Events\MessageSent($message));
```
