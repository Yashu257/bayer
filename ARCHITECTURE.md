# System Architecture

## 📊 High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                         USERS (7k+)                         │
│                    Browsers / Devices                       │
└────────────────────┬────────────────────────────────────────┘
                     │
                     │ HTTPS
                     │
        ┌────────────┴────────────┐
        │                         │
        ▼                         ▼
┌───────────────┐         ┌───────────────┐
│    VERCEL     │         │    RENDER     │
│   Frontend    │◄───────►│   Backend     │
│   Static      │   API   │   PHP + DB    │
└───────────────┘         └───────┬───────┘
        │                         │
        │                         │
        │                 ┌───────┴───────┐
        │                 │               │
        │                 ▼               ▼
        │         ┌──────────────┐  ┌─────────┐
        │         │   MySQL DB   │  │  Redis  │
        │         │  (Render)    │  │ (Cache) │
        │         └──────────────┘  └─────────┘
        │
        │
        ▼
┌───────────────┐
│   MUX CDN     │
│ Video Stream  │
└───────────────┘
```

## 🗂️ Directory Structure

```
pharma-webcast/
│
├── frontend/                     # Deploy to Vercel
│   ├── preview.html             # Main landing page
│   ├── preview.admin            # Admin dashboard
│   ├── preview-login.html       # Login page
│   ├── preview-landing.html     # Event landing
│   ├── preview-webcast.html     # Webcast room
│   ├── index.php                # PHP entry point
│   ├── router.php               # Request router
│   │
│   ├── api/                     # API proxy endpoints
│   │   ├── save-user.php        # Registration
│   │   ├── save-question.php    # Q&A
│   │   ├── save-selfie.php      # Photo booth
│   │   └── send-welcome.php     # Welcome email
│   │
│   ├── assets/                  # Static assets
│   │   ├── css/                 # Stylesheets
│   │   ├── js/                  # JavaScript
│   │   └── images/              # Images, icons
│   │
│   ├── vendor/                  # Third-party libraries
│   │   └── bootstrap-icons/     # Icons
│   │
│   └── uploads/                 # User uploads (selfies)
│
├── backend/                     # Deploy to Render
│   ├── app/                     # Application code
│   │   ├── Controllers/         # Request handlers
│   │   │   ├── Admin/          # Admin controllers
│   │   │   ├── Api/            # API controllers
│   │   │   └── Frontend/       # Frontend controllers
│   │   │
│   │   ├── Models/              # Database models
│   │   │   ├── User.php
│   │   │   ├── Event.php
│   │   │   ├── Registration.php
│   │   │   └── ...
│   │   │
│   │   ├── Services/            # Business logic
│   │   │   ├── AuthService.php
│   │   │   ├── EmailService.php
│   │   │   ├── EventService.php
│   │   │   └── ...
│   │   │
│   │   ├── Repositories/        # Data access
│   │   │   ├── UserRepository.php
│   │   │   ├── EventRepository.php
│   │   │   └── ...
│   │   │
│   │   ├── Middleware/          # Request filters
│   │   │   ├── AuthMiddleware.php
│   │   │   ├── CsrfMiddleware.php
│   │   │   ├── ThrottleMiddleware.php
│   │   │   └── ...
│   │   │
│   │   ├── Validators/          # Input validation
│   │   ├── DTOs/                # Data transfer objects
│   │   ├── Mail/                # Email templates
│   │   └── Views/               # PHP views
│   │
│   ├── config/                  # Configuration
│   │   ├── app.php
│   │   ├── auth.php
│   │   ├── mail.php
│   │   └── session.php
│   │
│   ├── core/                    # Framework core
│   │   ├── Database/            # DB connection
│   │   ├── Router/              # Routing
│   │   ├── Http/                # Request/Response
│   │   ├── Security/            # Security helpers
│   │   ├── Validation/          # Validators
│   │   └── Mail/                # Mail system
│   │
│   ├── routes/                  # Route definitions
│   │   ├── web.php              # Web routes
│   │   ├── api.php              # API routes
│   │   ├── admin.php            # Admin routes
│   │   └── middleware.php       # Middleware config
│   │
│   ├── storage/                 # Runtime storage
│   │   ├── logs/                # Application logs
│   │   ├── cache/               # Cache files
│   │   ├── sessions/            # Session files
│   │   └── temp/                # Temporary files
│   │
│   ├── .env                     # Environment variables
│   ├── composer.json            # PHP dependencies
│   └── run_migration.php        # Database migrations
│
├── .gitignore                   # Git ignore rules
├── README.md                    # Project overview
├── DEPLOYMENT.md                # Deployment guide
├── ARCHITECTURE.md              # This file
├── vercel.json                  # Vercel config
└── render.yaml                  # Render config
```

## 🔄 Request Flow

### Registration Flow
```
User Browser
    │
    │ 1. Fill registration form
    │
    ▼
Vercel Frontend (preview.html)
    │
    │ 2. Submit form
    │
    ▼
Vercel API Proxy (api/save-user.php)
    │
    │ 3. Forward to backend
    │
    ▼
Render Backend (API Controller)
    │
    │ 4. Validate input
    │
    ▼
Service Layer (RegistrationService)
    │
    │ 5. Process registration
    │
    ▼
Repository (RegistrationRepository)
    │
    │ 6. Save to database
    │
    ▼
MySQL Database
    │
    │ 7. Send confirmation email
    │
    ▼
Email Service (SMTP)
    │
    │ 8. Return success
    │
    ▼
User receives confirmation
```

### Webcast Streaming Flow
```
Admin
    │
    │ 1. Start event in admin panel
    │
    ▼
Backend updates event status
    │
    ▼
Users access webcast page
    │
    │ 2. Load preview-webcast.html
    │
    ▼
Page fetches Mux stream URL from backend
    │
    │ 3. Backend returns signed URL
    │
    ▼
Browser plays video from Mux CDN
    │
    │ 4. Direct connection (no backend load)
    │
    ▼
User watches stream
```

## 🔐 Security Layers

```
1. HTTPS (TLS 1.3)
   └─ All traffic encrypted
   
2. CSRF Protection
   └─ Token validation on all forms
   
3. XSS Protection
   └─ Input sanitization
   
4. Rate Limiting
   └─ Throttle requests per IP
   
5. SQL Injection Prevention
   └─ Parameterized queries
   
6. Session Security
   └─ HTTP-only cookies
   
7. Content Security Policy
   └─ Restrict resource loading
```

## 📊 Database Schema (Simplified)

```
┌──────────────┐
│    users     │
├──────────────┤
│ id           │
│ email        │
│ name         │
│ phone        │
│ created_at   │
└──────┬───────┘
       │
       │ 1:N
       │
       ▼
┌──────────────────┐
│  registrations   │
├──────────────────┤
│ id               │
│ user_id          │◄────┐
│ event_id         │     │
│ status           │     │
│ registered_at    │     │
└────────┬─────────┘     │
         │               │
         │               │
         ▼               │
┌──────────────┐         │
│    events    │         │
├──────────────┤         │
│ id           │         │
│ title        │         │
│ start_time   │         │
│ stream_url   │         │
└──────────────┘         │
                         │
┌──────────────┐         │
│  questions   │         │
├──────────────┤         │
│ id           │         │
│ user_id      │─────────┘
│ event_id     │
│ question     │
│ answered     │
└──────────────┘

┌──────────────┐
│   selfies    │
├──────────────┤
│ id           │
│ user_id      │
│ image_path   │
│ created_at   │
└──────────────┘

┌──────────────┐
│   admins     │
├──────────────┤
│ id           │
│ email        │
│ password     │
│ role         │
└──────────────┘
```

## 🚀 Scalability

### Current Capacity
- **Free Tier:** ~100 concurrent users
- **Starter Tier ($25/mo):** ~500 concurrent users
- **Standard Tier ($85/mo):** 7k+ concurrent users

### Bottlenecks & Solutions

| Component | Bottleneck | Solution |
|-----------|-----------|----------|
| Web Server | Single instance | Horizontal scaling (multiple instances) |
| Database | Connection limit | Connection pooling, read replicas |
| Sessions | File-based storage | Move to Redis |
| Static Assets | Server bandwidth | CDN (included in Vercel) |
| Video Streaming | Server load | Mux CDN (handles load) |

### For 7k Users Event

**Before Event:**
1. Upgrade Render to Standard plan
2. Upgrade database to Starter plan
3. Enable Redis for sessions
4. Run load tests

**During Event:**
- Monitor Render metrics
- Monitor database connections
- Check error logs
- Have rollback plan ready

**After Event:**
- Export attendance data
- Generate reports
- Send thank you emails
- Archive event data

## 🔧 Technology Stack

### Frontend
- **HTML5 / CSS3 / JavaScript**
- **Bootstrap Icons**
- **LocalStorage** (for preview mode)
- **Fetch API** (for backend calls)

### Backend
- **PHP 8.1+**
- **Custom MVC Framework**
- **MySQL 8.0** (database)
- **Composer** (dependencies)

### Infrastructure
- **Vercel** (frontend hosting)
- **Render** (backend hosting)
- **Mux** (video streaming CDN)
- **GitHub** (version control)

### Email
- **SMTP** (Gmail, SendGrid, etc.)
- **Custom email templates**

## 📈 Monitoring & Logs

### Frontend (Vercel)
- Real-time logs in dashboard
- Analytics (page views, performance)
- Error tracking

### Backend (Render)
- Application logs: `/backend/storage/logs/`
- Access logs: Render dashboard
- Error tracking: Sentry (optional)

### Database
- Query logs
- Slow query analysis
- Connection monitoring

## 🔄 Deployment Pipeline

```
Developer
    │
    │ 1. Code changes
    │
    ▼
Git commit
    │
    │ 2. Push to GitHub
    │
    ▼
GitHub (main branch)
    │
    ├─────────────┬─────────────┐
    │             │             │
    ▼             ▼             ▼
Vercel       Render       Render DB
(Auto)       (Auto)       (Manual)
    │             │             │
    │             │             │
    ▼             ▼             ▼
Frontend     Backend      Database
 Live         Live        Migrated
```

## 🎯 Next Steps

1. ✅ Code pushed to GitHub
2. ⏳ Deploy frontend to Vercel
3. ⏳ Deploy backend to Render
4. ⏳ Connect frontend to backend
5. ⏳ Run database migrations
6. ⏳ Create admin account
7. ⏳ Create test event
8. ⏳ Test registration flow
9. ⏳ Load testing
10. ⏳ Go live!
