import org.apache.mahout.cf.taste.recommender.RecommendedItem;

public class CollaborativeRecommendation {
	private RecommendedItem item;
	private long userId;
	private String explanation;
	
	public CollaborativeRecommendation(RecommendedItem item, long userId, String explanation){
		this.item = item;
		this.userId = userId;
		this.explanation = explanation;
	}
	

	public RecommendedItem getItem() {
		return item;
	}

	public void setItem(RecommendedItem item) {
		this.item = item;
	}

	public long getUserId() {
		return userId;
	}

	public void setUserId(int userId) {
		this.userId = userId;
	}

	public String getExplanation() {
		return explanation;
	}

	public void setExplanation(String explanation) {
		this.explanation = explanation;
	}
}
