# CS-160-Project

## Before you compile ##

In dLibLandmarks.cpp, on line 32, change to your own path to shape_predictor_68_face_landmarks.dat

`deserialize("<YOUR_PATH_TO>/CS-160-Project/CS-160-Project/shape_predictor_68_face_landmarks.dat") >> sp;`

In CMakeLists.txt, on line 7, change to your own path to your dlib install directory

`add_subdirectory(<YOUR_PATH_TO>/dlib-19.x/dlib dlib_build)`

## To compile the dlib/OpenCV main executable:

```
cd <YOUR_PATH_TO>/CS-160-Project/CS-160-Project/

mkdir build

cd build

cmake ..

cmake --build . --config Release
```
## To compile the pipeline executable:
```
cd <YOUR_PATH_TO>/CS-160-Project/CS-160-Project/

g++ -o pp processPipeline.cpp
```
## Pipeline Usage:
```
./pp <PATH_TO_VIDEO> main
```
