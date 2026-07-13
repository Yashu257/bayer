# Deployment Guide

## ✅ Code Pushed to GitHub
Repository: https://github.com/Yashu257/bayer.git

## 🚀 Frontend Deployment (Vercel)

### Step 1: Import to Vercel
1. Go to https://vercel.com/new
2. Click "Import Git Repository"
3. Select: `Yashu257/bayer`
4. Click "Import"

### Step 2: Configure Build Settings
- **Framework Preset:** Other
- **Root Directory:** `frontend`
- **Build Command:** (leave empty)
- **Output Directory:** (leave empty)
- **Install Command:** (leave empty)

### Step 3: Environment Variables
Add these in Vercel dashboard:
```
BACKEND_API_URL=https://your-backend.onrender.com
```

### Step 4: Deploy
- Click "Deploy"
- Wait for deployment
- Your frontend will be live at: `https://your-app.vercel.app`

---

## 🔧 Backend Deployment (Render)

### Step 1: Create Web Service
1. Go to https://dashboard.render.com/
2. Click "New +" → "Web Service"
3. Connect GitHub repository: `Yashu257/bayer`

### Step 2: Configure Service
- **Name:** pharma-webcast-backend
- **Region:** Choose closest to your users
- **Branch:** main
- **Root Directory:** `backend`
- **Runtime:** PHP
- **Build Command:** `composer install --no-dev --optimize-autoloader`
- **Start Command:** `php -S 0.0.0.0:$PORT -t ../frontend ../frontend/router.php`

### Step 3: Create Database
1. In Render dashboard, click "New +" → "PostgreSQL" (or MySQL)
2. Name: `pharma-webcast-db`
3. Plan: Starter (free)
4. Click "Create Database"

### Step 4: Environment Variables
In your Web Service settings, add:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-backend.onrender.com

# Database (auto-filled from Render database)
DB_HOST=<from database>
DB_PORT=<from database>
DB_DATABASE=<from database>
DB_USERNAME=<from database>
DB_PASSWORD=<from database>

# Mail (configure with your SMTP)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=noreply@pharmawebcast.com
MAIL_FROM_NAME=PharmaWebcast

# Session
SESSION_LIFETIME=120
SESSION_DRIVER=file

# Frontend URL
FRONTEND_URL=https://your-app.vercel.app
```

### Step 5: Deploy
- Click "Create Web Service"
- Wait for deployment (first deploy takes ~5-10 minutes)
- Your backend will be live at: `https://your-backend.onrender.com`

### Step 6: Run Database Migrations
After deployment, run migrations via Render Shell:
```bash
cd backend
php run_migration.php
```

---

## 🔗 Connect Frontend to Backend

### Update Vercel Environment Variable
1. Go to Vercel project settings
2. Update `BACKEND_API_URL` with your Render backend URL
3. Redeploy frontend

### Update API Endpoints in Frontend
Edit `frontend/api/*.php` files to point to Render backend:
```php
$BACKEND_URL = getenv('BACKEND_API_URL') ?: 'https://your-backend.onrender.com';
```

---

## ✅ Verify Deployment

### Frontend Checklist:
- [ ] Landing page loads: `https://your-app.vercel.app/preview.html`
- [ ] Admin page loads: `https://your-app.vercel.app/preview.admin`
- [ ] All assets load (images, CSS, JS)
- [ ] No console errors

### Backend Checklist:
- [ ] API responds: `https://your-backend.onrender.com/api/events`
- [ ] Database connected
- [ ] Admin login works
- [ ] Registration works
- [ ] Email sending works

---

## 🎯 Post-Deployment

### 1. Create Admin Account
```sql
INSERT INTO admins (email, password, name, role) 
VALUES ('admin@pharma.com', '$2y$10$hashed_password', 'Admin', 'super_admin');
```

### 2. Create Event
Use admin dashboard to create your first event

### 3. Test Registration Flow
1. Open landing page
2. Fill registration form
3. Verify email received
4. Check admin dashboard shows registration

---

## 📊 Monitoring

### Vercel Analytics
- Enable in Vercel dashboard → Analytics
- Monitor traffic, performance

### Render Metrics
- View in Render dashboard
- Monitor CPU, memory, response times

---

## 🆘 Troubleshooting

### Frontend Issues
- Check Vercel deployment logs
- Verify `BACKEND_API_URL` is set correctly
- Test API calls in browser console

### Backend Issues
- Check Render logs: Dashboard → Logs
- Verify database connection
- Check environment variables
- Test API endpoints directly

### Database Issues
- Verify migrations ran successfully
- Check connection string
- Test database access from Render shell

---

## 🔐 Security Notes

1. **Change default passwords** in production
2. **Enable CORS** properly between Vercel and Render
3. **Use environment variables** for all secrets
4. **Enable HTTPS** (automatic on Vercel and Render)
5. **Set up monitoring** and alerts

---

## 📈 Scaling for 7k Users

### Current Setup Handles:
- ✅ Up to ~100 concurrent users on free tier
- ✅ Basic event management
- ✅ Registration and webcast

### For 7k+ Users, Upgrade:
1. **Render Plan:** Starter → Standard ($25/month)
   - More CPU/RAM
   - Better performance

2. **Database Plan:** Free → Starter ($7/month)
   - More connections
   - Better performance

3. **Add Redis:** For session management
   - Enable Render Redis add-on
   - Update session driver to 'redis'

4. **CDN:** For static assets
   - Vercel includes CDN
   - Consider Cloudflare for additional caching

5. **Load Testing:** Before event
   ```bash
   # Install k6
   # Create load test script
   # Run: k6 run load-test.js
   ```

---

## 📞 Support

For deployment issues:
- Vercel Docs: https://vercel.com/docs
- Render Docs: https://render.com/docs
- GitHub Issues: https://github.com/Yashu257/bayer/issues
