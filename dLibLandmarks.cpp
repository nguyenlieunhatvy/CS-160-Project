//
// Created by Cyrus Javan on 3/13/18.
//
#include "main.h"

void *getLandmarksThread(void *threadArg){
    try
    {
        // We need a face detector.  We will use this to get bounding boxes for
        // each face in an image.
        frontal_face_detector detector = dlib::get_frontal_face_detector();

        // Get the parameters from threadArg
        thread_data * my_data = static_cast<thread_data *>(threadArg);
        int numImages = my_data->numImages;
        char** images = my_data->images;
        int  thread_id = my_data->thread_id;
        int start_idx = my_data->start_idx;
        int end_idx = my_data->end_idx;
        landmark_data* landmarkData = my_data->landmarkData;

        shape_predictor sp;
        //Use your own path to shape_predictor_68_face_landmarks.dat
        deserialize("/Users/cyrusjavan/Documents/cs160/dlib_examples/shape_predictor_68_face_landmarks.dat") >> sp;
        // Loop over all the images provided on the command line.
        for (int i = start_idx; i < end_idx; i++)
        {
            //Using load image
            array2d<rgb_pixel> img;
            load_image(img, images[i]);
            //pyramid_up(img,pyramid_down<2>());

            // Now tell the face detector to give us a list of bounding boxes
            // around all the faces in the image.
            std::vector<rectangle> faces = detector(img);

            // Now we will go ask the shape_predictor to tell us the pose of
            // each face we detected.
            for (unsigned long j = 0; j < faces.size(); ++j)
            {
                // Store all 68 landmarks into the index that corresponds
                // to the current image.
                full_object_detection shape = sp(img, faces[j]);
                for (int k = 0; k < NUM_LANDMARKS; k++){

                    landmarkData[i].points[k] = shape.part(k);
                }
            }
        }
    }
    catch (exception& e)
    {
        cout << "\nexception thrown!" << endl;
        cout << e.what() << endl;

    } return nullptr;
}
