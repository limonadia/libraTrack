# STAGE 1: Build the Angular App
FROM node:20-alpine AS build

# Set working directory inside the container
WORKDIR /app

# Copy ONLY package.json first to leverage Docker caching. 
# This avoids the build failure if package-lock.json is missing locally.
# IMPORTANT: Since the Docker build context is the project root, we must use the 'frontend/' prefix.
COPY frontend/package.json ./

# Install dependencies (this will generate package-lock.json inside the container)
RUN npm install

# Copy the rest of the frontend source code (all files inside libratrack/frontend/ to /app in the container)
COPY frontend/. .

# Build the application (this creates the 'dist' folder)
RUN npm run build

# STAGE 2: Serve the Built Files using Nginx
FROM nginx:alpine

# Copy the built files from the 'build' stage into Nginx's HTML directory
COPY --from=build /app/dist/libratrack-frontend /usr/share/nginx/html

# Nginx listens on port 80 by default
EXPOSE 80