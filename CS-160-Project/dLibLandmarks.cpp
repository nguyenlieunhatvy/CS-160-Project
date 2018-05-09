//
// Created by Cyrus Javan on 3/13/18.
//

#include "main.h"

void *getLandmarksThread(void *threadArg)
{
    int i;
    char **images;

    try
    {
        std::vector<cv::Mat> rgbChannels(3);

        // We need a face detector.  We will use this to get bounding boxes for
        // each face in an image.
        frontal_face_detector detector = dlib::get_frontal_face_detector();

        // Get the parameters from threadArg
        thread_data *my_data = static_cast<thread_data *>(threadArg);
        int numImages = my_data->numImages;
        images = my_data->images;
        int thread_id = my_data->thread_id;
        int start_idx = my_data->start_idx;
        int end_idx = my_data->end_idx;
        landmark_data *landmarkData = my_data->landmarkData;

        cv::CascadeClassifier face_cascade;
        if (!face_cascade.load(FACE_CASCADE_NAME))
        {
            printf("--(!)Error loading face cascade, please change face_cascade_name in source code.\n");
            exit(1);
        };

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
            if (faces.size() < 1)
            {
                std::vector<cv::Rect> faces1;
                face_cascade.detectMultiScale(debug_img, faces1, 1.1, 2, 0 | CV_HAAR_SCALE_IMAGE | CV_HAAR_FIND_BIGGEST_OBJECT, cv::Size(150, 150), debug_img.size());

                if (faces1.size() > 0)
                {
                    faces.clear();
                    for (unsigned long k = 0; k < faces1.size(); ++k)
                    {
                        faces.push_back(rectangle(faces1[k].x, faces1[k].y, faces1[k].x + faces1[k].width, faces1[k].y + faces1[k].height));
                    }
                }
            }

            // Store all 68 landmarks into the index that corresponds
            // to the current image.
            for (unsigned long j = 0; j < faces.size(); ++j)
            {
                // Making sure that the rectangle coordinates are positive
                if (faces[j].left() < 1)
                    faces[j].set_left(1);
                if (faces[j].width() < 1 || faces[j].left() + faces[j].width() >= debug_img.size().width)
                    faces[j].set_right(debug_img.size().width - 2);
                if (faces[j].top() < 1)
                    faces[j].set_top(1);
                if (faces[j].height() < 1 || faces[j].top() + faces[j].height() >= debug_img.size().height)
                    faces[j].set_bottom(debug_img.size().height - 2);

                full_object_detection shape = sp(img, faces[j]);
                // Store the points. After this loop, all 68 points are ready
                for (int k = 0; k < NUM_LANDMARKS - 2; k++)
                {
                    landmarkData[i].points[k] = shape.part(k);
                }
                //  Determine Yaw, Pitch & Roll
                // 2D image points.
                std::vector<cv::Point2d> image_points;
                image_points.push_back(toCv(shape.part(33))); // Nose tip
                image_points.push_back(toCv(shape.part(8)));  // Chin
                image_points.push_back(toCv(shape.part(45))); // Left eye left corner
                image_points.push_back(toCv(shape.part(36))); // Right eye right corner
                image_points.push_back(toCv(shape.part(54))); // Left Mouth corner
                image_points.push_back(toCv(shape.part(48))); // Right mouth corner

                // 3D model points.
                // These are generic values for a male face
                // Not always accurate
                std::vector<cv::Point3d> model_points;
                model_points.push_back(cv::Point3d(0.0f, 0.0f, 0.0f));          // Nose tip
                model_points.push_back(cv::Point3d(0.0f, -330.0f, -65.0f));     // Chin
                model_points.push_back(cv::Point3d(-225.0f, 170.0f, -135.0f));  // Left eye left corner
                model_points.push_back(cv::Point3d(225.0f, 170.0f, -135.0f));   // Right eye right corner
                model_points.push_back(cv::Point3d(-150.0f, -150.0f, -125.0f)); // Left Mouth corner
                model_points.push_back(cv::Point3d(150.0f, -150.0f, -125.0f));  // Right mouth corner

                // Camera internals
                double focal_length = debug_img.cols; // Approximate focal length.
                cv::Point2d center = cv::Point2d(debug_img.cols / 2, debug_img.rows / 2);
                cv::Mat camera_matrix = (cv::Mat_<double>(3, 3) << focal_length, 0, center.x, 0, focal_length, center.y, 0, 0, 1);
                cv::Mat dist_coeffs = cv::Mat::zeros(4, 1, cv::DataType<double>::type); // Assuming no lens distortion

                // Output rotation and translation
                cv::Mat rotation_vector; // Rotation in axis-angle form
                cv::Mat translation_vector;

                // Solve for pose
                // solvePnP will fill rotation_vector
                cv::solvePnP(model_points, image_points, camera_matrix, dist_coeffs, rotation_vector, translation_vector,CV_ITERATIVE);

                // yaw, pitch, roll formulas from:
                // http://citeseerx.ist.psu.edu/viewdoc/download?doi=10.1.1.371.6578&rep=rep1&type=pdf
                double pi = 3.14159265359;
                double yaw;
                double pitch;
                double roll;
                if (rotation_vector.at<double>(2, 0) != -1 && rotation_vector.at<double>(2, 0) != 1)
                {
                    pitch = -1 * sin(rotation_vector.at<double>(2, 0));
                    roll = atan2(rotation_vector.at<double>(2, 1) / cos(pitch), rotation_vector.at<double>(2, 2) / cos(pitch));
                    yaw = atan2(rotation_vector.at<double>(1, 0) / cos(pitch), rotation_vector.at<double>(0, 0) / cos(pitch));
                }
                else
                {
                    yaw = 0;
                    if(rotation_vector.at<double>(2, 0) == -1){
                        pitch = pi / 2;
                        roll = yaw + atan2(rotation_vector.at<double>(0, 1), rotation_vector.at<double>(0, 2));
                    }
                    else{
                        pitch = pi / 2;
                        roll = (-1 * yaw) + atan2((rotation_vector.at<double>(0, 1) * -1), (rotation_vector.at<double>(0, 2) * -1));
                    }
                }
                // Can ignore, used for drawing the line sticking out of nose
                //cv::Matx33d rotation;
                //cv::Rodrigues(rotation_vector, rotation);
                // // Project a 3D point (0, 0, 1000.0) onto the image plane.
                // // We use this to draw a line sticking out of the nose
                // std::vector<cv::Point3d> nose_end_point3D;
                // std::vector<cv::Point2d> nose_end_point2D;
                // nose_end_point3D.push_back(cv::Point3d(0, 0, 1000.0));
                // cv::projectPoints(nose_end_point3D, rotation_vector, translation_vector, camera_matrix, dist_coeffs, nose_end_point2D);
                // for (int i = 0; i < image_points.size(); i++)
                // {
                //     cv::circle(debug_img, image_points[i], 3, cv::Scalar(0, 0, 255), -1);
                // }
                // cv::line(debug_img, image_points[0], nose_end_point2D[0], cv::Scalar(255, 0, 0), 2);
                // cv::imwrite("./Input/Debug/debug" + string(&(images[i][8])), debug_img);

                // faceROI is the image for pupil dectection
                face = cv::Rect(faces[j].left(), faces[j].top(), faces[j].width(), faces[j].height());
                faceROI = frame_gray(face);

                //-- Find eye regions and draw them
                int leftLeft = landmarkData[i].points[36].x() - face.x;
                int leftTop = landmarkData[i].points[38].y() - face.y;
                int leftWidth = landmarkData[i].points[39].x() - landmarkData[i].points[36].x();
                if (leftWidth < 1)
                    leftWidth = 1;
                int leftHeight = landmarkData[i].points[41].y() - landmarkData[i].points[38].y();
                if (leftHeight < 1)
                    leftHeight = 1;
                cv::Rect leftEyeRegion(leftLeft, leftTop, leftWidth, leftHeight);

                int rightWidth = landmarkData[i].points[45].x() - landmarkData[i].points[42].x();
                if (rightWidth < 1)
                    rightWidth = 1;

                int rightHeight = landmarkData[i].points[46].y() - landmarkData[i].points[43].y();
                if (rightHeight < 1)
                    rightHeight = 1;
                cv::Rect rightEyeRegion(landmarkData[i].points[42].x() - face.x,
                                        landmarkData[i].points[43].y() - face.y,
                                        rightWidth,
                                        rightHeight);

                //-- Find Eye Centers
                cv::Point leftPupil = findEyeCenter(faceROI, leftEyeRegion, "Left Eye");
                leftPupil.x += leftEyeRegion.x + face.x;
                leftPupil.y += leftEyeRegion.y + face.y;

                cv::Point rightPupil = findEyeCenter(faceROI, rightEyeRegion, "Right Eye");
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
    catch (exception &e)
    {
        cout << "\nexception thrown!" << endl;
        cout << e.what() << endl;
        cout << images[i] << endl
             << endl;
    }
    return nullptr;
}
