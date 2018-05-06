cmake_minimum_required(VERSION 2.8.12)

project(CS160)

find_package( OpenCV REQUIRED )

add_subdirectory(/Users/cyrusjavan/Downloads/dlib-19.9/dlib dlib_build)
add_executable(main main.cpp dLibLandmarks.cpp openCVDrawing.cpp helpers.cpp findEyeCenter.cpp)
target_link_libraries(main dlib::dlib ${OpenCV_LIBS})
