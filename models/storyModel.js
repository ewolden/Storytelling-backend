.factory('Story', function (Organisation) {
 
   /**
   * Constructor, with class name
   */
  function Story(storyId, title, author, imageId, introduction, description, rights, municipality, county) {
    // Public properties, assigned to the instance ('this')
    this.storyId = storyId;
    this.title = title;
    this.author = author;
    this.imageId = imageId;
    this.introduction = introduction;
    this.description = description; 
    this.rights = rights;
    this.municipality = municipality;
    this.county = county;
  }
 
  /**
   * Public method, assigned to prototype
   */
  Story.prototype.getStoryId = function () {
    return this.storyId;
  };
  
  

 
  /**
   * Return the constructor function
   */
  return Story;
})
