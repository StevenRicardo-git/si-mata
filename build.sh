set -e

echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "Generating application key..."
php artisan key:generate --force

echo "Running migrations..."
php artisan migrate --force --no-interaction

echo "Build completed successfully!"