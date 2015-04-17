# Storytelling-docker

For å stoppe kjørende fil:
#docker stop [TRYKK TAB HER]
For å bygge, ewolden/storytelling er her bare et navn for docker bildet:
#docker build -t ewolden/storytelling .

For å kjøre, mypass blir SQL passord, 80:80 mapper port 80 til http og 3306:3306 mapper port 3306 til mysql
#docker run -d --volumes-from Storytelling-DBdata -p 80:80 -p 3306:3306 -e MYSQL_PASS="mypass" ewolden/storytelling
