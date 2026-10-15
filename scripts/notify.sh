#!/usr/bin/env bash

# Helper script to send ntfy push notification
MESSAGE="${1:-Task completed successfully}"
TITLE="${2:-7 Series Kamkaj Update}"
TAGS="${3:-white_check_mark}"

TOPIC=$(grep "^NTFY_TOPIC=" .env | cut -d '=' -f2 | tr -d ' "' || echo "ntfy-7sdkamkaj-notify-8f92b7")
if [ -z "$TOPIC" ]; then
    TOPIC="ntfy-7sdkamkaj-notify-8f92b7"
fi

curl -s \
  -H "Title: $TITLE" \
  -H "Tags: $TAGS" \
  -H "Priority: default" \
  -d "$MESSAGE" \
  "https://ntfy.sh/$TOPIC" > /dev/null

echo "Notification sent to https://ntfy.sh/$TOPIC"
