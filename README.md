# Pharma Webcast Platform

A multi-user event platform for pharmaceutical webcasts with 7k+ attendee capacity.

## 🏗️ Architecture

```
pharma-webcast/
├── frontend/          # Static HTML/CSS/JS (Deploy to Vercel)
│   ├── preview.html   # Main landing page
│   ├── preview.admin  # Admin dashboard
│   ├── assets/        # CSS, JS, images
│   ├── api/           # API proxy endpoints
│   └── vendor/        # Bootstrap icons, etc.
│
├── backend/           # PHP Backend (Deploy to Render)
│   ├── app/           # Controllers, Models, Services
│   ├── config/        # Configuration files
│   ├── core/          # Framework core
│   ├── routes/        # Route definitions
│   ├── storage/       # Logs, cache, sessions
│   └── .env           # Environment variables
│
├── vercel.json        # Vercel deployment config
└── render.yaml        # Render deployment config
```

## 🚀 Deployment

### Frontend (Vercel)
1. Push to GitHub
2. Import repository in Vercel
3. Set Root Directory: `frontend`
4. Deploy

### Backend (Render)
1. Push to GitHub
2. Create new Web Service in Render
3. Connect GitHub repository
4. Render will auto-detect `render.yaml`
5. Create PostgreSQL/MySQL database
6. Deploy

## 🔧 Local Development

### Frontend
```bash
cd frontend
php -S localhost:8973
```

### Backend
```bash
cd backend
composer install
php -S localhost:8000 -t ../frontend ../frontend/router.php
```

## 📊 Features

- ✅ Event landing page with registration
- ✅ Live webcast streaming (Mux integration)
- ✅ Real-time Q&A
- ✅ Polls and surveys
- ✅ Selfie booth with photo frames
- ✅ Admin dashboard
- ✅ Attendance tracking
- ✅ Email campaigns
- ✅ Multi-user support (7k+ capacity)

## 🔐 Environment Variables

See `backend/.env.example` for required variables.

## 📝 License

Proprietary - All rights reserved
