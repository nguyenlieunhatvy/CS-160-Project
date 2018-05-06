//
// Created by Cyrus Javan on 3/13/18.
//

#include "main.h"

void *getLandmarksThread(void *threadArg) {
    int i;
    char** images;

    try {
        std::vector<cv::Mat> rgbChannels(3);
        
        // We need a face detector.  We will use this to get bounding boxes for
        // each face in an image.
        frontal_face_detector detector = dlib::get_frontal_face_detector();

        // Get the parameters from threadArg
        thread_data * my_data = static_cast<thread_data *>(threadArg);
        int numImages = my_data->numImages;
        images = my_data->images;
        int thread_id = my_data->thread_id;
        int start_idx = my_data->start_idx;
        int end_idx = my_data->end_idx;
        landmark_data* landmarkData = my_data->landmarkData;

        cv::CascadeClassifier face_cascade;
        if( !face_cascade.load( FACE_CASCADE_NAME ) ){ printf("--(!)Error loading face cascade, please change face_cascade_name in source code.\n"); exit(1); };

        shape_predictor sp;
        // Use your own path to shape_predictor_68_face_landmarks.dat
        deserialize("/Users/cyrusjavan/cs160-project/CS-160-Project/CS-160-Project/shape_predictor_68_face_landmarks.dat") >> sp;
        
        
        cv::Rect face;
        cv::Mat faceROI;

        // Ignore these
        //int facescount = 0;
        //int faces1count = 0;
        
        // Loop over all the images provided on the command line.
        for (i = start_idx; i < end_idx; i++)
        {
            // Using load image
            cv::Mat debug_img = cv::imread(images[i]);
            cv::Mat frame_gray;
        
            // Get gray_scale image
            cv::split(debug_img, rgbChannels);
            frame_gray = rgbChannels[2];
            
            // Convert to Dlib image
            cv_image<unsigned char> img(frame_gray);

            // Now tell the face detector to give us a list of bounding boxes
            // around all the faces in the image.
            std::vector<rectangle> faces = detector(img);

            // If Dlib cannot find a face, trying OpenCV
            if(faces.size() < 1) {
                std::vector<cv::Rect> faces1;
                face_cascade.detectMultiScale( debug_img, faces1, 1.1, 2, 0|CV_HAAR_SCALE_IMAGE|CV_HAAR_FIND_BIGGEST_OBJECT, cv::Size(150, 150), debug_img.size() );
                
                if(faces1.size() > 0){
                    faces.clear();
                    for (unsigned long k = 0; k < faces1.size(); ++k) {
                        faces.push_back(rectangle(faces1[k].x, faces1[k].y, faces1[k].x + faces1[k].width, faces1[k].y + faces1[k].height));
                    }
                }
            }

            // Store all 68 landmarks into the index that corresponds
            // to the current image.
            for (unsigned long j = 0; j < faces.size(); ++j)
            {
                // Making sure that the rectangle coordinates are positive
                if(faces[j].left() < 1) faces[j].set_left(1);
                if(faces[j].width() < 1 || faces[j].left() + faces[j].width() >= debug_img.size().width) faces[j].set_right(debug_img.size().width - 2);
                if(faces[j].top() < 1) faces[j].set_top(1);
                if(faces[j].height() < 1 || faces[j].top() + faces[j].height() >= debug_img.size().height) faces[j].set_bottom(debug_img.size().height - 2);

                full_object_detection shape = sp(img, faces[j]);

                // faceROI is the image for pupil dectection
                face = cv::Rect(faces[j].left(), faces[j].top(), faces[j].width(), faces[j].height());  
                faceROI = frame_gray(face);
                
                // Store the points. After this loop, all 68 points are ready
                for (int k = 0; k < NUM_LANDMARKS - 2; k++) {
                    landmarkData[i].points[k] = shape.part(k);
                }

                //-- Find eye regions and draw them                
                int leftLeft = landmarkData[i].points[36].x() - face.x;
                int leftTop = landmarkData[i].points[38].y() - face.y;
                int leftWidth = landmarkData[i].points[39].x() - landmarkData[i].points[36].x();
                if(leftWidth < 1) leftWidth = 1;
                int leftHeight = landmarkData[i].points[41].y() - landmarkData[i].points[38].y();
                if(leftHeight < 1) leftHeight = 1;
                cv::Rect leftEyeRegion(leftLeft, leftTop, leftWidth, leftHeight);
                
                int rightWidth = landmarkData[i].points[45].x() - landmarkData[i].points[42].x();
                if(rightWidth < 1) rightWidth = 1;

                int rightHeight = landmarkData[i].points[46].y() - landmarkData[i].points[43].y();
                if(rightHeight < 1 ) rightHeight = 1;
                cv::Rect rightEyeRegion(landmarkData[i].points[42].x() - face.x,
                                        landmarkData[i].points[43].y() - face.y,
                                        rightWidth,
                                        rightHeight);
                
                //-- Find Eye Centers
                cv::Point leftPupil = findEyeCenter(faceROI,leftEyeRegion,"Left Eye");
                leftPupil.x += leftEyeRegion.x + face.x;
                leftPupil.y += leftEyeRegion.y + face.y;
                
                cv::Point rightPupil = findEyeCenter(faceROI,rightEyeRegion,"Right Eye");
                rightPupil.x += rightEyeRegion.x + face.x;
                rightPupil.y += rightEyeRegion.y + face.y;
                
                //-- Store Eye Centers in 68 and 69
                landmarkData[i].points[NUM_LANDMARKS - 2] = dlib::point(leftPupil.x, leftPupil.y);
                landmarkData[i].points[NUM_LANDMARKS - 1] = dlib::point(rightPupil.x, rightPupil.y);
            }
                //Ignore these
                /*
                //circle(faceROI, leftPupil, 3, 1234, 3);
                //circle(faceROI, rightPupil, 3, 1234, 3);
                //cv::merge(rgbChannels, frame_gray);
                //imwrite(images[i],frame_gray);
                //cout << "test" << endl;
                */
                /*
                cv::rectangle(faceROI, leftEyeRegion, 1234);
                cv::rectangle(faceROI, rightEyeRegion, 1234);
                cv::imwrite("./Input/Debug/debug" + string(&(images[i][8])), faceROI);
                */
                /*if(faces.size() < 1 && faces1.size() > 0) {
                //++facescount;
                //cout << images[i] << ": " << faces.size() << " " << faces1.size() << endl;
                }*/
                // pyramid_up(img);
                // pyramid_up(img,pyramid_down<2>());

                //array2d<rgb_pixel> img_rgb;
                //array2d<rgb_pixel> img;
                //cv::cvtColor(cv::imread(images[i]), frame_gray, CV_BGR2GRAY);
        }
        //cout << "Faces: " << facescount << endl;
        //cout << "Faces1: " << faces1count << endl;
    }
    catch(exception& e)
    {
        cout << "\nexception thrown!" << endl;
        cout << e.what() << endl;
        cout << images[i] << endl << endl;

    } return nullptr;
}
