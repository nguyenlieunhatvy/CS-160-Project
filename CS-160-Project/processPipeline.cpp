#include <iostream>
using namespace std;
int main(int argc, char **argv){
    if (argc > 3) {printf("Process only takes 2 arg\n");
    exit(1);}
    char splitString[1280];
    snprintf(&splitString, sizeof(splitString) - 1, "ffmpeg -i %s -vf fps=30 out%04d.png", argv[1]);
	std::system(splitString);
	snprintf(&splitString, sizeof(splitString) - 1, "%s ./Input/*.png", argv[2]);
    std::system(splitString);
    snprintf(&splitString, sizeof(splitString) - 1, "ffmpeg -r 30 -start_number 1 -f image2 -i ./Output/OUTPUT-out%04d.png -vcodec mjpeg -qscale 1  %s", argv[1]);
    std::system(splitString);

	std::system("rm ./Input/*.png");
    std::system("rm ./Output/*.png");
}
