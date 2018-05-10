We try to improve the performance. Therefore, in the pipeline, we store the 68 points and yaw, pitch, and roll in RAM memory.
Database operations are expensive, so we try not to use as much as possible.
We understand that storing data in database will enhance team collaboration, but for our team specifically, Cyrus and I work together on the same source code for image processing. Hence, we pass our data through functions’ parameters.
In the future, if we want to scale our program, and the team gets bigger, we will need to store our processed data in the database.