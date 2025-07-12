# FITBOT AI Chatbot Plugin - Complete Documentation

## Table of Contents
1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Initial Configuration](#initial-configuration)
4. [Admin Interface Guide](#admin-interface-guide)
5. [Frontend Display](#frontend-display)
6. [Content Management](#content-management)
7. [WooCommerce Integration](#woocommerce-integration)
8. [Troubleshooting](#troubleshooting)
9. [Advanced Features](#advanced-features)
10. [FAQ](#faq)

---

## 1. Introduction

The **FITBOT AI Chatbot** is a comprehensive WordPress plugin designed for fitness and health websites. It integrates with OpenAI's GPT-4 Turbo API to provide personalized fitness advice, nutrition guidance, and health recommendations based on user subscription plans.

### Key Features
- **AI-Powered Chat**: Uses OpenAI GPT-4 Turbo for intelligent responses
- **Subscription-Based Access**: Integrates with WooCommerce Subscriptions
- **Multi-Language Support**: Auto-detects user language and responds accordingly
- **Responsive Design**: Works perfectly on mobile, tablet, and desktop
- **Content Management**: Upload recipes, workouts, PDFs, and motivation messages
- **Plan-Based Features**: Different features for Start (€5/mo), Pro (€12/mo), and VIP (€24/mo) plans

---

## 2. Installation

### Step 1: Download the Plugin
Download the `fitbot-ai-chatbot.zip` file provided to you.

### Step 2: Upload to WordPress
1. Log in to your WordPress admin dashboard
2. Navigate to **Plugins → Add New**
3. Click **Upload Plugin**
4. Choose the `fitbot-ai-chatbot.zip` file
5. Click **Install Now**
6. Click **Activate Plugin**

### Step 3: Verify Installation
After activation, you should see **FITBOT** in your WordPress admin menu.

---

## 3. Initial Configuration

### Step 1: Configure OpenAI API
1. Go to **FITBOT → Settings**
2. In the **API Settings** tab:
   - Enter your **OpenAI API Key**
   - Set **Chatbot Delay** (default: 3000ms)
   - Click **Test API Connection** to verify

### Step 2: WooCommerce Integration
1. In the **WooCommerce** tab:
   - Enter **Start Plan Product ID** (for €5/mo plan)
   - Enter **Pro Plan Product ID** (for €12/mo plan)
   - Enter **VIP Plan Product ID** (for €24/mo plan)

### Step 3: UI Customization
1. In the **UI Settings** tab:
   - Choose **Primary Color** for the chatbot
   - Set **Chatbot Position** (bottom-right, bottom-left, etc.)
   - Configure **Greeting Message**
   - Enable/disable **Sound Effects** and **Typing Indicators**

### Step 4: Save Settings
Click **Save Settings** to apply all configurations.

---

## 4. Admin Interface Guide

### Settings Page
Access via **FITBOT → Settings**

#### API Settings Tab
- **OpenAI API Key**: Your OpenAI API key for GPT-4 Turbo
- **Chatbot Delay**: Delay before chatbot appears (milliseconds)
- **Test API Connection**: Verify your API key works

#### WooCommerce Tab
- **Start Plan Product ID**: WooCommerce product ID for Start plan
- **Pro Plan Product ID**: WooCommerce product ID for Pro plan
- **VIP Plan Product ID**: WooCommerce product ID for VIP plan

#### UI Settings Tab
- **Primary Color**: Main color for chatbot interface
- **Chatbot Position**: Where chatbot appears on screen
- **Greeting Message**: First message users see
- **Enable Sound**: Sound notifications for new messages
- **Enable Typing Indicator**: Shows when bot is "typing"

#### Upgrade Messages Tab
- Customize messages shown when users need to upgrade plans

### Content Management Page
Access via **FITBOT → Content**

#### Recipes Tab
- Upload and manage recipe content
- Set meal types (breakfast, lunch, dinner)
- Assign to subscription plans
- Add nutrition information

#### Workouts Tab
- Upload workout routines
- Set difficulty levels and duration
- Assign equipment requirements
- Tag with conditions (thyroid-safe, etc.)

#### Motivation Tab
- Add daily motivation messages
- Schedule or randomize delivery

#### PDFs Tab
- Upload downloadable resources
- Meal plans, workout guides, etc.
- Available for VIP plan users

#### Bulk Upload Tab
- Upload multiple content items at once
- Drag-and-drop interface
- Progress tracking

---

## 5. Frontend Display

### How the Chatbot Appears

The chatbot automatically appears on **every page** of your website after the configured delay (default: 3 seconds).

#### Visual Elements
1. **Floating Button**: Appears in bottom-right corner (or configured position)
2. **Chat Window**: Opens when button is clicked
3. **Responsive Design**: Adapts to mobile, tablet, and desktop screens

#### User Experience Flow
1. **Page Load**: User visits any page on your website
2. **Delay**: Chatbot waits 3 seconds (configurable)
3. **Appearance**: Floating button slides in
4. **Interaction**: User clicks button to open chat
5. **Greeting**: Bot displays personalized greeting message
6. **Conversation**: User can ask questions and get AI responses

### Customization Options

#### Position Settings
- **bottom-right**: Default position
- **bottom-left**: Left side of screen
- **top-right**: Upper right corner
- **top-left**: Upper left corner

#### Color Themes
- Set primary color in admin settings
- Automatically generates complementary colors
- Maintains accessibility standards

#### Mobile Optimization
- Touch-friendly buttons
- Optimized text size
- Swipe gestures supported
- Full-screen chat on small devices

---

## 6. Content Management

### Adding Recipes

1. Go to **FITBOT → Content → Recipes**
2. Click **Add New Recipe**
3. Fill in:
   - **Title**: Recipe name
   - **Content**: Description and instructions
   - **Ingredients**: List of ingredients
   - **Prep Time**: Preparation time in minutes
   - **Cook Time**: Cooking time in minutes
   - **Servings**: Number of servings
   - **Nutrition**: Calories, carbs, fats, protein
4. **Assign Plan Level**: Start, Pro, or VIP
5. **Set Meal Type**: Breakfast, lunch, dinner
6. **Add Conditions**: Thyroid-safe, keto-friendly, etc.
7. Click **Publish**

### Adding Workouts

1. Go to **FITBOT → Content → Workouts**
2. Click **Add New Workout**
3. Fill in:
   - **Title**: Workout name
   - **Content**: Workout description
   - **Duration**: Time in minutes
   - **Difficulty**: Beginner, intermediate, advanced
   - **Equipment**: Required equipment
   - **Exercises**: List of exercises
4. **Assign Plan Level**: Pro or VIP (workouts not available for Start)
5. **Set Workout Type**: Cardio, strength, flexibility, etc.
6. **Add Conditions**: Condition-specific modifications
7. Click **Publish**

### Adding PDFs

1. Go to **FITBOT → Content → PDFs**
2. Click **Add New PDF**
3. Fill in:
   - **Title**: PDF name
   - **Content**: Description
   - **Upload File**: Select PDF file
4. **Assign to VIP Plan** (PDFs only available for VIP users)
5. Click **Publish**

### Bulk Upload

1. Go to **FITBOT → Content → Bulk Upload**
2. **Drag and drop** multiple files
3. **Select content type** for each file
4. **Assign plan levels** in bulk
5. Click **Process Upload**
6. Monitor progress bar

---

## 7. WooCommerce Integration

### Setting Up Subscription Plans

#### Create WooCommerce Products
1. Go to **Products → Add New** in WooCommerce
2. Create three subscription products:

**Start Plan (€5/mo)**
- Product Type: Simple Subscription
- Price: €5
- Billing Period: Monthly
- Features: 1 recipe/day, basic Q&A

**Pro Plan (€12/mo)**
- Product Type: Simple Subscription
- Price: €12
- Billing Period: Monthly
- Features: 3 recipes/day, workouts, nutrition facts

**VIP Plan (€24/mo)**
- Product Type: Simple Subscription
- Price: €24
- Billing Period: Monthly
- Features: Unlimited recipes, health answers, PDFs

#### Configure Plugin Settings
1. Note the **Product IDs** from WooCommerce
2. Go to **FITBOT → Settings → WooCommerce**
3. Enter the Product IDs for each plan
4. Save settings

### How Plan Detection Works

The plugin automatically:
1. **Checks user login status**
2. **Queries active subscriptions**
3. **Determines highest plan level**
4. **Restricts features accordingly**
5. **Shows upgrade prompts** when needed

### Upgrade Flow

When users request features outside their plan:
1. **Bot explains limitation**
2. **Suggests appropriate upgrade**
3. **Provides upgrade link**
4. **Redirects to WooCommerce checkout**

---

## 8. Troubleshooting

### Common Issues

#### Chatbot Not Appearing
**Possible Causes:**
- JavaScript errors on page
- Plugin not activated
- Conflicting plugins

**Solutions:**
1. Check browser console for errors
2. Deactivate other plugins temporarily
3. Switch to default theme to test
4. Clear cache if using caching plugins

#### API Connection Failed
**Possible Causes:**
- Invalid OpenAI API key
- Network connectivity issues
- API quota exceeded

**Solutions:**
1. Verify API key in OpenAI dashboard
2. Test API connection in plugin settings
3. Check OpenAI account billing status
4. Contact OpenAI support if needed

#### Subscription Detection Not Working
**Possible Causes:**
- Incorrect Product IDs
- WooCommerce Subscriptions not active
- User not logged in

**Solutions:**
1. Verify Product IDs in WooCommerce
2. Ensure WooCommerce Subscriptions is active
3. Test with logged-in user account
4. Check subscription status in WooCommerce

#### Mobile Display Issues
**Possible Causes:**
- Theme CSS conflicts
- Viewport meta tag missing
- JavaScript errors on mobile

**Solutions:**
1. Add viewport meta tag to theme
2. Check mobile-specific CSS
3. Test on actual mobile devices
4. Use browser developer tools mobile emulation

### Debug Mode

Enable WordPress debug mode to see detailed error messages:

```php
// Add to wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Check debug logs in `/wp-content/debug.log`

---

## 9. Advanced Features

### Custom CSS Styling

Add custom styles to your theme's CSS:

```css
/* Customize chatbot button */
.fitbot-chatbot-button {
    background-color: #your-color !important;
    border-radius: 50% !important;
}

/* Customize chat window */
.fitbot-chat-window {
    max-width: 400px !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3) !important;
}

/* Mobile-specific styles */
@media (max-width: 768px) {
    .fitbot-chat-window {
        width: 100% !important;
        height: 100% !important;
    }
}
```

### JavaScript Hooks

Use JavaScript hooks to customize behavior:

```javascript
// Before chatbot initializes
document.addEventListener('fitbot_before_init', function(e) {
    console.log('Chatbot initializing...');
});

// After message sent
document.addEventListener('fitbot_message_sent', function(e) {
    console.log('Message sent:', e.detail.message);
});

// After response received
document.addEventListener('fitbot_response_received', function(e) {
    console.log('Response received:', e.detail.response);
});
```

### PHP Filters

Customize plugin behavior with WordPress filters:

```php
// Modify greeting message
add_filter('fitbot_greeting_message', function($message, $user_plan) {
    if ($user_plan === 'vip') {
        return 'Welcome back, VIP member! How can I help you today?';
    }
    return $message;
}, 10, 2);

// Modify API request parameters
add_filter('fitbot_gpt_request_params', function($params) {
    $params['temperature'] = 0.8; // More creative responses
    return $params;
});
```

---

## 10. FAQ

### General Questions

**Q: Do I need an OpenAI account?**
A: Yes, you need an OpenAI account and API key to use the chatbot functionality.

**Q: What's the cost of using OpenAI API?**
A: OpenAI charges based on token usage. Typical costs are $0.01-0.03 per conversation.

**Q: Can I use this without WooCommerce?**
A: The plugin requires WooCommerce Subscriptions for plan management. Without it, all users will be treated as having no plan.

**Q: Is the chatbot GDPR compliant?**
A: The plugin stores conversation logs locally. You're responsible for your privacy policy and data handling practices.

### Technical Questions

**Q: Can I customize the chatbot appearance?**
A: Yes, through admin settings and custom CSS. The plugin provides extensive customization options.

**Q: Does it work with caching plugins?**
A: Yes, but you may need to exclude the AJAX endpoints from caching.

**Q: Can I translate the plugin?**
A: The plugin is translation-ready. The AI responses will automatically match the user's language.

**Q: What happens if OpenAI is down?**
A: The plugin will show a friendly error message and log the issue for debugging.

### Subscription Questions

**Q: How are plan limits enforced?**
A: The plugin tracks daily usage and blocks requests when limits are reached, showing upgrade prompts instead.

**Q: Can users downgrade plans?**
A: Plan changes are handled through WooCommerce Subscriptions. The plugin will automatically adjust features.

**Q: What if a user's subscription expires?**
A: The plugin will detect expired subscriptions and treat the user as having no plan.

---

## Support

For technical support or questions about the FITBOT AI Chatbot plugin:

1. **Check this documentation** for common solutions
2. **Enable debug mode** to identify specific issues
3. **Test with default theme** to rule out conflicts
4. **Contact the developer** with specific error messages and steps to reproduce

---

## Changelog

### Version 1.0.0
- Initial release
- OpenAI GPT-4 Turbo integration
- WooCommerce Subscriptions support
- Responsive design
- Multi-language support
- Content management system
- Plan-based feature restrictions

---

*This documentation covers the complete functionality of the FITBOT AI Chatbot plugin. Keep this guide handy for reference during setup and ongoing management of your AI-powered fitness chatbot.*
