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

import org.apache.mahout.cf.taste.common.TasteException;
import org.apache.mahout.cf.taste.impl.common.FastIDSet;
import org.apache.mahout.cf.taste.model.DataModel;
import org.apache.mahout.cf.taste.model.PreferenceArray;

/* 
 * This is more or less copy-paste taken from this commit: https://github.com/apache/mahout/commit/d141c8e887904122a2b3cb4bf94851e7401d807d 
 * Needed to do this because the current version of Mahout doesn't support recommendation of already consumed items.
 * Seems like it's going to be supported in the next version though.
 */

public final class CustomPreferredItemsNeighborhoodCandidateItemsStrategy extends CustomAbstractCandidateItemsStrategy implements CustomCandidateItemsStrategy {

  /**
   * returns all items that have not been rated by the user and that were preferred by another user
   * that has preferred at least one item that the current user has preferred too
   */
  @Override
  protected FastIDSet doGetCandidateItems(long[] preferredItemIDs, DataModel dataModel, boolean includeKnownItems)
    throws TasteException {
    FastIDSet possibleItemsIDs = new FastIDSet();
    for (long itemID : preferredItemIDs) {
      PreferenceArray itemPreferences = dataModel.getPreferencesForItem(itemID);
      int numUsersPreferringItem = itemPreferences.length();
      for (int index = 0; index < numUsersPreferringItem; index++) {
        possibleItemsIDs.addAll(dataModel.getItemIDsFromUser(itemPreferences.getUserID(index)));
      }
    }
    if (!includeKnownItems) {
      possibleItemsIDs.removeAll(preferredItemIDs);
    }
    return possibleItemsIDs;
  }

public FastIDSet getCandidateItems(long userID,
		PreferenceArray preferencesFromUser, DataModel dataModel)
		throws TasteException {
	// TODO Auto-generated method stub
	return null;
}

}