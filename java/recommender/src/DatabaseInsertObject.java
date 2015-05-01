
public class DatabaseInsertObject {
	
	private int userId;
	private String storyId;
	private String explanation;
	private int false_recommend;
	private int type_of_recommendation;
	private int ranking;
	private double estimatedValue;
	
	public DatabaseInsertObject(int userId, String storyId, String explanation,
			int false_recommend, int type_of_recommendation, int ranking, double estimatedValue) {
		super();
		this.userId = userId;
		this.storyId = storyId;
		this.explanation = explanation;
		this.false_recommend = false_recommend;
		this.type_of_recommendation = type_of_recommendation;
		this.ranking = ranking;
		this.estimatedValue = estimatedValue;
	}
	
	public int getUserId() {
		return userId;
	}

	public String getStoryId() {
		return storyId;
	}
	public String getExplanation() {
		return explanation;
	}
	public int getFalse_recommend() {
		return false_recommend;
	}
	public int getType_of_recommendation() {
		return type_of_recommendation;
	}
	public int getRanking() {
		return ranking;
	}
	public double getEstimatedValue() {
		return estimatedValue;
	}
	
}
