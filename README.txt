=== Plugin Name ===
Plugin Name: Techno Chatbot
Requires at least: 3.0.1
Tested up to: 7.0
Stable tag: 1.0.6

== Description ==
Techno Chatbot is a real-time chatbot and live chat system built as a WordPress plugin by Technodream Webdesign. It allows automated bot responses and seamless transfer to human administrators for live chat.

The system is designed to be lightweight, secure, and scalable for customer interaction on WordPress websites.
---
## Features
- Automated chatbot responses
- Real-time live chat with administrators
- Bot-to-admin transfer capability
- WebSocket-based real-time messaging
- Chat session management
- Admin online/offline detection
- Message history saving
- Secure AJAX communication
- Input sanitization and nonce validation
- Responsive chat interface
- Rate limiting for message saving
---
## Tech Stack
### Backend
- PHP (WordPress Plugin)
- MySQL
- WordPress AJAX API
### Frontend
- JavaScript (Vanilla JS)
- HTML5
- CSS3
### Realtime Server
- Node.js
- Socket.IO
- WebSockets
---
## Security
The system includes the following security measures:
- WordPress nonce verification
- Input sanitization before saving
- HTML tag stripping from messages
- Rate limiting per IP address
- Secure WebSocket authentication
- Token-based connection validation
---
## Customization
- You can customize the following components:
- Chatbot response logic
- Chat UI appearance
- Admin dashboard behavior
- Message storage logic
- Transfer rules and escalation behavior

== Changelog ==

= 1.0.0 =
* Initial release

= 1.0.1 =
* Test Updater release

= 1.0.2 =
* Updated default message
* Made the FAQ answer field WYSIWYG
* Allowed chatbot return bot answer with HTML

= 1.0.3 =
* Fixed AI Prompt for no response
* Updated chatbot default replies
* Fixed Licensing

= 1.0.4 =
* Fixed Licensing capabilities

= 1.0.5 =
* Updated text defaults
* Fixed Css

= 1.0.6 =
* Fixed responsive Css
* Added admin email notification for live transfer request