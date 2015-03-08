//BYGGER VIDERE PÅ services.js SOM ER LAGET I FRONT-END
//BURDE KANSKJE LEGGES INN I services.js? ELLERS MÅ MODULEN ('starter.services') 
//FÅ NYTT NAVN :)
angular.module('backend.services', ['ngSanitize'])
.factory("Story", function ($sce) {

	 /**
	 * Constructor, with class name
	 */
	 function Story(storyData) {

	 	this.storyId = storyData.storyId;
	 	this.title = storyData.title;

		//Returns an array, use author[0]
		this.author = storyData.creatorList;
		this.imageList = storyData.imageList;
		this.introduction = storyData.introduction;

		//EXAMPLE VIDEO-URL: "http://mm01.dimu.no/multimedia/012FwwCj.mp4?mmid=012FwwCj"
		this.videoList = storyData.videoList;

		// NEEDS <p ng-bind-html="story.text"></p> where <p> {{story.text}} </p> is in story.html
		// to make html tags from story work
		this.text = $sce.trustAsHtml(storyData.theStory);

		//EXAMPLE AUDIO-URL: "http://mm01.dimu.no/multimedia/012QsXh9.mp3?mmid=012QsXh9"
		this.audioList = storyData.audioList;
		this.rights = storyData.rights;
		this.municipality = storyData.municipality;
		this.county = storyData.county;
		this.institution = storyData.institution;

		this.subcategoryList = storyData.subCategoryNames;

		//Tror ikke denne trenger å hentes her??
		this.subcategoryIDs = storyData.subCategoryList;
		this.subjectList = storyData.subjectList;
		this.url = $sce.trustAsUrl("http://www.digitaltfortalt.no/things/thing/H-DF/"+this.storyId);

		this.updateMedia();
	}

	//TRENGER VI Å HENTE UT BILDELISTE I DET HELE TATT NÅR BILDEVISNING BRUKER INDEKS OG IKKE ID?
	//BARE HENTE ANTALL BILDER?
	/** Adds the imageurl to imageList */
	Story.prototype.updateMedia = function(){
		//BLIR LITT RART, HELE BILDET VISES IKKE PÅ STORYVIEW. BØR EKSPERIMENTERES MED HEIGHTxWIDHT?
		if(this.imageList != null)
			for(var i = 0; i < this.imageList.length; i++){
				this.imageList[i] = $sce.trustAsResourceUrl("http://media31.dimu.no/media/image/H-DF/"+this.storyId+"/"+i+"?byIndex=true&height=400&width=400");
			}
		}

		/** Public method, assigned to prototype */
		Story.prototype.getStoryId = function () {
			return this.storyId;
		};

		/** Return the constructor function */
		return Story;
	})


/**Handles communication with backend*/
.factory("Requests", function ($http) {
	var req = {
		method: 'POST',
		url: '../../requests/controller.php',
		headers: {'Content-Type': 'application/json'} // 'Content-Type': application/json???
	}


	/* DETTE MÅ BRUKES I Controllere:
 	Requests."metode"().then(function(response){
    		$scope."detsomskalbrukes" = new Story(response.data); eller bare response.data
    	}); TUNGVINT MÅTE?? :/*/

	return {
		/**Retrieves single story from digitalt fortalt*/
		getStory: function(id){
			req.data = {
				type: "getStory",
				storyId: id };
			//Burde sikkert bruke .success osv, men det så mye finere ut uten :)
			return $http(req);
		},

		//PRØVER Å HENTE DE 20 FØRSTE HISTORIENE FRA DATABASEN NÅ OG LEGGE TIL I LISTE
		//BRUKER GETALLSTORIES METODE I DBHELPER. SKAL SLETTES ETTERPÅ

		/**Retrieves multiple stories from the database, now returns 500 error when
		* story doesn't have pictures*/
		getMultipleStories: function(idArray) {
			req.data = { type: "getStories" };
			return $http(req);
		},

		//MENINGEN AT DET SKAL VÆRE MED DESCRIPTION. NOE SOM SKAL LEGGES TIL HER
		//ELLEr ET ANNET STED?
		/** Adds user rating to a story */
		addRating: function(storyId, userId, rating){
			req.data = {
				type: "rating",
				storyId: storyId,
				userId: userId,
				rating: rating};
			$http(req)
		},
		
		addTag: function(tagName, userId, storyId){
			req.data = {
				type: "addTag",
				userId: userId,
				storyId: storyId,
				tagName: tagName
			};
			$http(req)
		}
	}
});