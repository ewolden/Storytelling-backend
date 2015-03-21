/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements.  See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

package recommender;

import java.util.List;

import org.apache.mahout.cf.taste.common.TasteException;
import org.apache.mahout.cf.taste.impl.common.FastIDSet;
import org.apache.mahout.cf.taste.model.DataModel;
import org.apache.mahout.cf.taste.model.PreferenceArray;
import org.apache.mahout.cf.taste.recommender.IDRescorer;
import org.apache.mahout.cf.taste.recommender.RecommendedItem;
import org.apache.mahout.cf.taste.recommender.Recommender;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import com.google.common.base.Preconditions;

/* 
 * This is more or less copy-paste taken from this commit: https://github.com/apache/mahout/commit/d141c8e887904122a2b3cb4bf94851e7401d807d 
 * Needed to do this because the current version of Mahout doesn't support recommendation of already consumed items.
 * Seems like it's going to be supported in the next version though.
 */

public abstract class CustomAbstractRecommender implements CustomRecommender {
  
  private static final Logger log = LoggerFactory.getLogger(CustomAbstractRecommender.class);
  
  private final DataModel dataModel;
  private final CustomCandidateItemsStrategy candidateItemsStrategy;
  
  protected CustomAbstractRecommender(DataModel dataModel, CustomCandidateItemsStrategy candidateItemsStrategy) {
    this.dataModel = Preconditions.checkNotNull(dataModel);
    this.candidateItemsStrategy = Preconditions.checkNotNull(candidateItemsStrategy);
  }

  protected CustomAbstractRecommender(DataModel dataModel) {
    this(dataModel, (CustomCandidateItemsStrategy) getDefaultCandidateItemsStrategy());
  }

  protected static CustomCandidateItemsStrategy getDefaultCandidateItemsStrategy() {
    return new CustomPreferredItemsNeighborhoodCandidateItemsStrategy();
  }


  /**
   * <p>
   * Default implementation which just calls
   * {@link Recommender#recommend(long, int, org.apache.mahout.cf.taste.recommender.IDRescorer)}, with a
   * {@link org.apache.mahout.cf.taste.recommender.Rescorer} that does nothing.
   * </p>
   */
  public List<RecommendedItem> recommend(long userID, int howMany) throws TasteException {
    return recommend(userID, howMany, false);
  }

  /**
   * <p>
   * Default implementation which just calls
   * {@link Recommender#recommend(long, int, org.apache.mahout.cf.taste.recommender.IDRescorer)}, with a
   * {@link org.apache.mahout.cf.taste.recommender.Rescorer} that does nothing.
   * </p>
   */
  public List<RecommendedItem> recommend(long userID, int howMany, boolean includeKnownItems) throws TasteException {
    return recommend(userID, howMany, includeKnownItems);
  }
  
  /**
   * <p> Delegates to {@link Recommender#recommend(long, int, IDRescorer, boolean)}
   */
  public List<RecommendedItem> recommend(long userID, int howMany, IDRescorer rescorer) throws TasteException{
    return recommend(userID, howMany,false);  
  }
  
  /**
   * <p>
   * Default implementation which just calls {@link DataModel#setPreference(long, long, float)}.
   * </p>
   *
   * @throws IllegalArgumentException
   *           if userID or itemID is {@code null}, or if value is {@link Double#NaN}
   */
  public void setPreference(long userID, long itemID, float value) throws TasteException {
    Preconditions.checkArgument(!Float.isNaN(value), "NaN value");
    log.debug("Setting preference for user {}, item {}", userID, itemID);
    dataModel.setPreference(userID, itemID, value);
  }
  
  /**
   * <p>
   * Default implementation which just calls {@link DataModel#removePreference(long, long)} (Object, Object)}.
   * </p>
   *
   * @throws IllegalArgumentException
   *           if userID or itemID is {@code null}
   */
  public void removePreference(long userID, long itemID) throws TasteException {
    log.debug("Remove preference for user '{}', item '{}'", userID, itemID);
    dataModel.removePreference(userID, itemID);
  }
  
  public DataModel getDataModel() {
    return dataModel;
  }

  /**
   * @param userID
   *          ID of user being evaluated
   * @param preferencesFromUser
   *          the preferences from the user
   * @param includeKnownItems
   *          whether to include items already known by the user in recommendations
   * @return all items in the {@link DataModel} for which the user has not expressed a preference and could
   *         possibly be recommended to the user
   * @throws TasteException
   *           if an error occurs while listing items
   */
  protected FastIDSet getAllOtherItems(long userID, PreferenceArray preferencesFromUser, boolean includeKnownItems)
      throws TasteException {
    return candidateItemsStrategy.getCandidateItems(userID, preferencesFromUser, dataModel, includeKnownItems);
  }
  
}
