//
// Created by Cyrus Javan on 3/13/18.
//

#include "main.h"

static void draw_point (cv::Mat &img, cv::Point2f fp, cv::Scalar color)
{
    cv::circle (img, fp, 3, color, CV_FILLED, CV_AA, 0);
}

// Draws the delaunay triangles on the img
static void draw_delaunay (cv::Mat &img, cv::Subdiv2D &subdiv, cv::Scalar delaunay_color)
{
    std::vector<cv::Vec6f> triangleList;
    subdiv.getTriangleList(triangleList);
    std::vector<cv::Point> pt(3);
    cv::Size size = img.size();
    cv::Rect rect(0,0, size.width, size.height);

    for (size_t i = 0; i < triangleList.size(); i++)
    {
        cv::Vec6f t = triangleList[i];
        pt[0] = cv::Point(cvRound(t[0]), cvRound(t[1]));
        pt[1] = cv::Point(cvRound(t[2]), cvRound(t[3]));
        pt[2] = cv::Point(cvRound(t[4]), cvRound(t[5]));

        // Draw rectangles completely inside the image.
        if (rect.contains(pt[0]) && rect.contains(pt[1]) && rect.contains(pt[2]))
        {
            cv::line (img, pt[0], pt[1], delaunay_color, 1, CV_AA, 0);
            cv::line (img, pt[1], pt[2], delaunay_color, 1, CV_AA, 0);
            cv::line (img, pt[2], pt[0], delaunay_color, 1, CV_AA, 0);
        }
    }
}

// OpenCV part of the pipeline
// Draws delaunay triangles for the given image file
static void run (face_landmark_node *face_landmark_list_head, char* fileName)
{
    face_landmark_node *face_landmark_element;
    cv::Scalar delaunay_color(255,0,0), points_color(0, 255, 0); // Note: delaunay_color and points_color are in BGR (BLUE, GREEN, RED) format
    cv::Mat source_image;
    cv::Size source_image_resolution;
    char input_filename[1280], output_filename[1280]; // 1024 bytes for path + 256 bytes for filename  = 1280 bytes.

    memset (&input_filename, 0, sizeof(input_filename) - 1);
    memset (&output_filename, 0, sizeof(output_filename) - 1);
    strncpy (&input_filename[0], fileName, sizeof(input_filename) - 1);
    snprintf (&output_filename[0], sizeof(output_filename) - 1,  "./Output/OUTPUT-%s", &input_filename[8]);
    
    if (input_filename[0] != '\0')
    {
        cout << "Path: " << output_filename << endl;
        source_image = cv::imread (&input_filename[0]);
        if (!source_image.empty())
        {
            source_image_resolution = source_image.size();
            cv::Rect rect(0, 0, source_image_resolution.width, source_image_resolution.height);
            cv::Subdiv2D subdiv(rect);

            face_landmark_element = face_landmark_list_head;
            
            while (face_landmark_element != NULL)
            {
                subdiv.insert(cv::Point2f(face_landmark_element->x, face_landmark_element->y));
                face_landmark_element = face_landmark_element->next;
            }
            
            draw_delaunay (source_image, subdiv, delaunay_color);
            face_landmark_element = face_landmark_list_head;
            
            while (face_landmark_element != NULL)
            {
                //cout << "X: " << face_landmark_element->x << endl;
                //cout << "Y: " << face_landmark_element->y << endl;
                draw_point (source_image, cv::Point2f(face_landmark_element->x, face_landmark_element->y), points_color);
                face_landmark_element = face_landmark_element->next;
            }
            //cout << "End" << endl;

            //scout << "writing to " << output_filename << endl;
            cv::imwrite (&output_filename[0], source_image);
        }
    }
}

void drawDelauneyTrianglesThread(landmark_data* landmarks, char** fileNames, int size) {
    for (int i = 1; i < size; i++){
        face_landmark_node *face_landmark_list_head;
        face_landmark_list_head = NULL;
        face_landmark_list_head = buildLandmarkListThread(landmarks[i], face_landmark_list_head);
        
        // Draw triangles for current frame
        run (face_landmark_list_head, fileNames[i]);
    }
}

face_landmark_node * buildLandmarkListThread(landmark_data landmarks, face_landmark_node* face_landmark_list_head){
    for (int i = 0; i < NUM_LANDMARKS; i++){
        face_landmark_list_head = add_face_landmark_element (face_landmark_list_head,
                                                             1,
                                                             i,
                                                             landmarks.points[i].x(), landmarks.points[i].y());
    }
    return face_landmark_list_head;
}

// Adds a face landmark element to the given linked list
// Returns the head of the linked list
face_landmark_node * add_face_landmark_element (face_landmark_node *face_landmark_list_head,
                                                int frame, int indice,
                                                float pixel_location_x, float pixel_location_y)
{
    face_landmark_node *new_face_landmark_element, *face_landmark_element, *previous_face_landmark_element;

    new_face_landmark_element = (face_landmark_node *) malloc (sizeof (face_landmark_node));
    if (new_face_landmark_element != NULL)
    {
        new_face_landmark_element->frame = frame;
        new_face_landmark_element->indice = indice;
        new_face_landmark_element->x = pixel_location_x;
        new_face_landmark_element->y = pixel_location_y;
        new_face_landmark_element->next = NULL;
        if (face_landmark_list_head != NULL)
        {
            face_landmark_element = face_landmark_list_head;
            while (face_landmark_element->next != NULL)
            {
                face_landmark_element = face_landmark_element->next;
            }
            face_landmark_element->next = new_face_landmark_element;
        }
        else
        {
            face_landmark_list_head = new_face_landmark_element;
        }
    }
    return face_landmark_list_head;
}

