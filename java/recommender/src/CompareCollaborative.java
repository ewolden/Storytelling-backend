import java.util.Comparator;

public class CompareCollaborative implements Comparator<CollaborativeRecommendation> {
    @Override
    public int compare(CollaborativeRecommendation o1, CollaborativeRecommendation o2) {
    	return -Float.compare(o1.getItem().getValue(),o2.getItem().getValue());
    }
}
