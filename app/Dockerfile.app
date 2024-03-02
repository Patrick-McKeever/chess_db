# Use an official PHP runtime as a parent image
FROM php:7.4-apache

RUN docker-php-ext-install mysqli

# Set the working directory to /var/www/html (default for Apache)
WORKDIR /var/www/html

# Install any necessary dependencies for your PHP application
# For example, if you are using Composer, you can uncomment the following lines
# RUN apt-get update && apt-get install -y \
#     git \
#     && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js and npm for the frontend
# You can use an official Node.js image as a base image if you prefer
RUN apt-get update && apt-get install -y \
    nodejs \
    npm


# Copy the React frontend code into the container
COPY frontend-src/package*.json /var/www/html/

# Install frontend dependencies
RUN npm install

# Copy the React frontend code into the container
COPY frontend-src/ /var/www/html/

# Build the React app
RUN npm run build

RUN cp -r /var/www/html/build/* /var/www/html

# Copy the PHP backend code into the container
# Keep this last, since npm build takes much longer
COPY backend-src/ /var/www/html/

# Expose the port the app runs on
EXPOSE 80

# Start Apache when the container runs
CMD ["apache2-foreground"]
