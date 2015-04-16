CREATE TABLE user 
	(userId				INT			NOT NULL  AUTO_INCREMENT,
	 mail				VARCHAR(255),
	 age_group			TINYINT,
	 gender			TINYINT,
	 use_of_location		BOOLEAN		DEFAULT 0,

	PRIMARY KEY (userId));

CREATE TABLE story
	(storyId			VARCHAR(15)	NOT NULL ,
	#Mahout needs numerical ids
	 numericalId		INT	NOT NULL,
	 title				VARCHAR(255),
	 author				VARCHAR(255),
	 date				VARCHAR(255),
 	 institution			VARCHAR(255),
	 introduction			TEXT,
	 lastChangedTime		VARCHAR(255),	

	PRIMARY KEY (storyId));

CREATE TABLE preference_value
	(userId				INT			NOT NULL,
	storyId				VARCHAR(15) NOT NULL,
	numericalId			INT NOT NULL,
	time_stamp			TIMESTAMP NOT NULL,
	preferenceValue		DECIMAL(8,5)   NOT NULL,
	
	PRIMARY KEY (userId, storyId),
	FOREIGN KEY (userId) REFERENCES user(userId),
	FOREIGN KEY (storyId) REFERENCES story(storyId));
	
	
CREATE TABLE category
	(categoryId			INT			NOT NULL  AUTO_INCREMENT,
	 categoryName		VARCHAR(255)	NOT NULL,

	PRIMARY KEY (categoryId));

INSERT INTO `category`(`categoryName`) VALUES ('art and design'); 
INSERT INTO `category`(`categoryName`) VALUES ('architecture');
INSERT INTO `category`(`categoryName`) VALUES ('archeology');
INSERT INTO `category`(`categoryName`) VALUES ('history');
INSERT INTO `category`(`categoryName`) VALUES ('local traditions and food');
INSERT INTO `category`(`categoryName`) VALUES ('nature and adventure');
INSERT INTO `category`(`categoryName`) VALUES ('literature');
INSERT INTO `category`(`categoryName`) VALUES ('music');
INSERT INTO `category`(`categoryName`) VALUES ('science and technology');

#Get subcategoryId from Digitalt fortalt, check type
CREATE TABLE subcategory
	(subcategoryId		VARCHAR(255)	NOT NULL ,
	 subcategoryName		VARCHAR(255),

	PRIMARY KEY (subcategoryId));

CREATE TABLE category_mapping
	(categoryId			INT			NOT NULL,
	 subcategoryId		VARCHAR(255)	NOT NULL,

	PRIMARY KEY (categoryId, subcategoryId),
	FOREIGN KEY (categoryId) REFERENCES category(categoryId),
	FOREIGN KEY (subcategoryId) REFERENCES subcategory(subcategoryId));

#Media format: picture, audio, video
CREATE TABLE media_format
	(mediaId			INT 			NOT NULL  AUTO_INCREMENT,
	 mediaName			VARCHAR(255) 	NOT NULL,

	PRIMARY KEY (mediaId));

INSERT INTO `media_format`(`mediaName`) VALUES ('picture'); #mediaId=1
INSERT INTO `media_format`(`mediaName`) VALUES ('audio'); #mediaId=2
INSERT INTO `media_format`(`mediaName`) VALUES ('video'); #mediaId=3

#Which media a story contains
CREATE TABLE story_media
	(storyId			VARCHAR(15	)	NOT NULL,
	 mediaId			INT			NOT NULL,

	PRIMARY KEY (storyId, mediaId),
	FOREIGN KEY (storyId) REFERENCES story(storyId),
	FOREIGN KEY (mediaId) REFERENCES media_format(mediaId));

#The user’s preference of category.
CREATE TABLE category_preference
	(userId				INT	 		NOT NULL,
	 categoryId			INT			NOT NULL,

	PRIMARY KEY (userId, categoryId),
	FOREIGN KEY (userId) REFERENCES user(userId),
	FOREIGN KEY (categoryId) REFERENCES category(categoryId));

CREATE TABLE notification
	(notificationId			INT			NOT NULL  AUTO_INCREMENT,
	 message			TEXT,
	 viewed			BOOLEAN		DEFAULT 0,
	 userId				INT,

	PRIMARY KEY (notificationId),
	FOREIGN KEY (userId) REFERENCES user(userId));

#Which stories a notification is connected to
CREATE TABLE story_notification
	(notificationId			INT			NOT NULL,
	 storyId			VARCHAR(15)	NOT NULL,

	PRIMARY KEY (notificationId, storyId),
	FOREIGN KEY (notificationId) REFERENCES notification(notificationId),
	FOREIGN KEY (storyId) REFERENCES story(storyId));

CREATE TABLE story_subcategory
	(storyId			VARCHAR(15)	NOT NULL,
	 subcategoryId		VARCHAR(255)	NOT NULL,

	PRIMARY KEY (storyId, subcategoryId),
	FOREIGN KEY (storyId) REFERENCES story(storyId),
	FOREIGN KEY (subcategoryId) REFERENCES subcategory(subcategoryId));

#Connects stories and tags in Digitalt fortalt
CREATE TABLE story_dftags
	(storyId			VARCHAR(15)	NOT NULL,
	 DFTagName			VARCHAR(255)	NOT NULL,

	PRIMARY KEY (storyId, DFTagName),
	FOREIGN KEY (storyId) REFERENCES story(storyId));

CREATE TABLE user_tag
	(userId				INT			NOT NULL,
	 tagName			VARCHAR (255)	NOT NULL,

	PRIMARY KEY (userId, tagName),
	FOREIGN KEY (userId) REFERENCES user(userId));

#Tags connected to story by the user
CREATE TABLE user_storytag
	(userId				INT			NOT NULL,
	 storyId			VARCHAR(15)	NOT NULL,
	 tagName			VARCHAR(255)	NOT NULL,

	PRIMARY KEY (userId, storyId, tagName),
	FOREIGN KEY (userId) REFERENCES user(userId),
	FOREIGN KEY (storyId) REFERENCES story(storyId));

#States: to-be-read, read, rejected, rated, recomended
#Is read=rated?
CREATE TABLE state
	(stateId			INT			NOT NULL AUTO_INCREMENT,
	  stateName			VARCHAR(255)	NOT NULL,

	PRIMARY KEY (stateId));

INSERT INTO `state`(`stateName`) VALUES ('recommended'); 
INSERT INTO `state`(`stateName`) VALUES ('rejected');
INSERT INTO `state`(`stateName`) VALUES ('to-be-read');
INSERT INTO `state`(`stateName`) VALUES ('read');
INSERT INTO `state`(`stateName`) VALUES ('rated');
INSERT INTO `state`(`stateName`) VALUES ('not interested');

#Every story that has been linked to a user by recommendation
CREATE TABLE stored_story
	(userId				INT			NOT NULL,
	 storyId			VARCHAR(15)	NOT NULL,
	 explanation			TEXT,
	 rating				INT			DEFAULT 0,		
	 #To know if the recommendation is true or false (suprise me-stories).
	 false_recommend		BOOLEAN		NOT NULL,
	 #Content-based eller collaborative (hybrid) filtering.
	 type_of_recommendation	BOOLEAN		NOT NULL,

	PRIMARY KEY (userId, storyId),
	FOREIGN KEY (userId) REFERENCES user(userId),
	FOREIGN KEY (storyId) REFERENCES story(storyId));


#For research purpose: when is a story read, rejected, reviewed, how many times read, etc.
# Other purpose: find what state a story is in at the moment (=latest timestamp)
CREATE TABLE story_state
	(recordedStateId 		INT			NOT NULL 	AUTO_INCREMENT,
	 storyId			VARCHAR(15)	NOT NULL,
	 userId				INT			NOT NULL,
	 stateId			INT			NOT NULL,
	 point_in_time			TIMESTAMP		NOT NULL,

	PRIMARY KEY (recordedStateId),
	FOREIGN KEY (storyId) REFERENCES story(storyId),
	FOREIGN KEY (userId) REFERENCES user(userID),
	FOREIGN KEY (stateId) REFERENCES state(stateID));

#For research purpose: know how often a user enters the app, and what actions are taken #during the time (find this in story_state table based on timestamps)
CREATE TABLE user_usage
	(usageId 			INT			NOT NULL   AUTO_INCREMENT,
	 userId				INT 			NOT NULL,
	 opensApp			TIMESTAMP		NOT NULL,

	PRIMARY KEY (usageId),
	FOREIGN KEY (userId) REFERENCES user(userId));
