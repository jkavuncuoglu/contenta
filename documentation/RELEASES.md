# Releases and Forking

## Download a Release

- Download the latest packaged build from Google Drive:
  - Placeholder link: https://drive.google.com/your-release-link
  - Replace this link with your actual release URL.

After download:
- Extract the archive into a directory
- Configure `.env` and run:
  composer install
  npm install
  php artisan key:generate
  php artisan migrate
  npm run build

## Fork on GitHub

1. Fork the repository to your GitHub account.
2. Clone your fork:
   git clone <your-fork-url>
3. Set up the project: see `SETUP.md`

## Updating

- Pull latest changes from upstream
- Run database migrations and rebuild assets when dependencies change
