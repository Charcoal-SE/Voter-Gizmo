<?php //placeholder
// Basic structure:
//  1. Get user's access_token
//  2. Call /posts with a filter that includes whether the user has upvoted/downvoted
//  3. Throw out all the ones that the user has
//  4. Display them one-by-one to the user, with an upvote button, a downvote button, and a 'later' button.
//  5. Perform the vote (no action for 'later') and show the next item in the queue
//  6. Maybe check to see if the user has any votes left anyway?