# Use an official centos runtime as a parent image
FROM centos

# maintainer
MAINTAINER longcheng

# shell mkdir application.
SHELL ["mkdir", "application"]

# Set the working directory to /application
WORKDIR /application

# Copy the current directory contents into the container at /application
# ADD . /application
COPY web_images /application/

# Make port available to the world outside this container
EXPOSE 9090

# Run application when the container launches
CMD ["./web_images"]