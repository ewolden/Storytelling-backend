# storytelling

#Setting up a persistent docker environment: 

Download the dockerfile, create a image from the docker file: 

docker build -t imageName .

Create a volume for docker to store databases in (only needs to be done the first time): 

docker create -v /dbdata --name Storytelling-DBdata imageName

Start the docker image with importing from the database storage:

docker run -d --volumes-from Storytelling-DBdata -p 80:80 -p 3306:3306 -e MYSQL_PASS="chosen password" imageName

Read as: docker run -demonize --import from database volume -port exposed extrernal:internal -port exposed external:internal -environment variable variable="" imageName

To stop the running container:

docker stop containerName

To remove old containers:

docker rm containerName


