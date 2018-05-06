# CS-160-Project

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
