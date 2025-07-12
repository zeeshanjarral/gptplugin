# FITBOT AI Chatbot Plugin - Development Plan

## Plugin Structure
```
/fitbot-ai-chatbot/
├── fitbot.php                          # Main plugin file
├── /includes/
│   ├── class-fitbot-core.php          # Core plugin functionality
│   ├── class-gpt-chat-api.php         # OpenAI GPT-4 Turbo API integration
│   ├── class-woo-subscription-check.php # WooCommerce subscription validation
│   ├── class-recipe-workout-loader.php # Content management system
│   ├── class-custom-post-types.php    # Custom post types for content
│   └── class-ajax-handlers.php        # AJAX request handlers
├── /assets/
│   ├── js/
│   │   ├── chatbot.js                 # Frontend chatbot functionality
│   │   └── admin.js                   # Admin panel JavaScript
│   ├── css/
│   │   ├── chatbot.css               # Chatbot UI styles
│   │   └── admin.css                 # Admin panel styles
│   └── images/
│       └── fitbot-icon.png           # Chatbot icon
├── /admin/
│   ├── class-admin-menu.php          # Admin menu setup
│   ├── class-content-upload-page.php # Content management interface
│   └── class-settings-page.php       # Plugin settings page
└── /templates/
    └── chatbot-widget.php            # Chatbot HTML template
```

## Core Features Implementation

### 1. AI Chatbot Integration
- **OpenAI GPT-4 Turbo API Integration**
  - Secure API key storage in WordPress options
  - Language auto-detection using GPT
  - Context-aware responses based on user subscription
  - Conversation history management

### 2. WooCommerce Subscription Integration
- **Subscription Plans**
  - Start Plan (€5/mo) - Product ID mapping
  - Pro Plan (€12/mo) - Product ID mapping  
  - VIP Plan (€24/mo) - Product ID mapping
- **User Access Control**
  - Real-time subscription status checking
  - Feature restriction based on active plan
  - Graceful handling of expired subscriptions

### 3. Feature Access by Plan
- **Start Plan Features**
  - 1 recipe per day (breakfast OR dinner)
  - Basic Q&A responses
  - Daily usage tracking
- **Pro Plan Features**
  - 3 recipes per day (breakfast + lunch + dinner)
  - Daily written workouts
  - Nutrition facts display
- **VIP Plan Features**
  - Unlimited recipes
  - Smart health answers (thyroid, bloating, sleep)
  - Condition-based workouts
  - PDF downloads
  - Specialized meal plans (keto, hormone-friendly)

### 4. Custom Content Management
- **Custom Post Types**
  - Recipes (with meal type, plan level, condition tags)
  - Workouts (home/gym, condition-based tags)
  - Motivation messages
  - PDF resources
- **Admin Interface**
  - Bulk upload functionality
  - Content categorization system
  - Preview and editing capabilities

### 5. Smart Upgrade Suggestions
- **Upgrade Prompts**
  - Detect feature requests outside user's plan
  - Generate contextual upgrade messages
  - Direct links to WooCommerce upgrade pages
  - Track conversion metrics

### 6. Multilingual Support
- **GPT-Powered Translation**
  - Auto-detect user input language
  - Respond in detected language
  - Maintain context across languages
  - Support for major European languages

### 7. Frontend Chatbot UI
- **Modern Chat Interface**
  - Floating button (bottom-right)
  - Expandable chat bubble
  - Typing indicators
  - Message history
  - Mobile-responsive design
- **AJAX Communication**
  - Real-time message exchange
  - Non-blocking user experience
  - Error handling and retry logic

## Technical Implementation Details

### Database Schema
- Custom tables for conversation history
- User usage tracking (daily limits)
- Content metadata storage

### Security Measures
- Nonce verification for AJAX requests
- Input sanitization and validation
- Rate limiting for API calls
- Secure API key storage

### Performance Optimization
- Caching for frequently accessed content
- Lazy loading of chat interface
- Optimized database queries
- CDN-ready asset structure

### WordPress Integration
- Hooks and filters for extensibility
- Proper enqueueing of scripts/styles
- Translation-ready strings
- WordPress coding standards compliance

## Development Phases
1. Core plugin structure and activation
2. Custom post types and admin interface
3. WooCommerce integration and user validation
4. OpenAI API integration and chat logic
5. Frontend UI development
6. Testing and optimization
7. Documentation and deployment
