#include <iostream>
#include <fstream>
#include <regex>
using namespace std;
// processPipeline is the entry point for the processing pipeline
// Usage: ./processPipeline PATH_TO_VIDEO PATH_TO_MAIN_PROCESSING_EXECUTABLE
// This executable expects to be called from the CS-160-PROJECT folder, which contains the build folder
int main(int argc, char **argv){
    if (argc > 3) {printf("Process only takes 2 arg\n");
    exit(1);}
    char splitString[1280];
    char frameRate[32];
    
    //extract frame rate
    snprintf(splitString, sizeof(splitString) - 1, "ffprobe -v 0 -of csv=p=0 -select_streams 0 -show_entries stream=r_frame_rate %s > frameRateTemp.txt", argv[1]);
	std::system(splitString);// 30/1
	ifstream inFile;
	inFile.open("frameRateTemp.txt");
	if (!inFile) {
    	cerr << "Unable to open file frameRateTemp.txt";
    	exit(1);   // call system to stop
	}
	inFile >> frameRate;

    // extract the frames
    snprintf(splitString, sizeof(splitString) - 1, "ffmpeg -i %s -vf fps=%s ./build/Input/out%%04d.png", argv[1], frameRate);
	std::system(splitString);

	// process the frames
	snprintf(splitString, sizeof(splitString) - 1, "cd build; ./%s ./Input/*.png; cd ..;", argv[2]);
	// cout << "The string !!" << splitString << "\n"; // For debugging
    std::system(splitString);
    
	// Reassemble the frames and overwrite the given video
    snprintf(splitString, sizeof(splitString) - 1, "ffmpeg -y -r %s -start_number 1 -f image2 -i ./build/Output/OUTPUT-out%%04d.png -qscale 1  %s",frameRate, argv[1]);
 	// cout << "The string !!" << splitString << "\n"; // For debugging
    std::system(splitString);

	// Finally clean up the Input and Output directory
	std::system("rm ./build/Input/*.png");
	std::string thumbDest = std::regex_replace(argv[1], std::regex("\\.\\.\\/video\\/([0-9\\-_]+)\\.\\w+"), "../thumbnail/$1.png");
    snprintf(splitString, sizeof(splitString) - 1, "mv ./build/Output/OUTPUT-out0010.png %s", thumbDest.c_str());
    std::system(splitString); //"mv ./build/Output/OUTPUT-out0010.png %s")
    snprintf(splitString, sizeof(splitString) - 1, "convert %s -resize 57x42! %s", thumbDest.c_str(), thumbDest.c_str());
    std::system(splitString);
    std::system("rm ./build/Output/*.png");
}
