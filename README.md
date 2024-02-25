SWIKOSearch is a tool which allows you to search through EXMARaLDA files. This repo currently contains:
 - convertToSearchable.py : a script which takes .exb files and turns them HTML files which are easily searched by regular expression and,
 - corpusSearcherTools.php : a couple of PHP functions which parse user input into regular expressions that can be applied to the previously generated HTML files.

To use this tool, 
 1) clone this repo and its submodules.
 2) place your EXMARaLDA files into the "input/", and create a directory named "searchableFiles/"
 3) run "python convertToSearchable.py". This will generate some HTML files in "searchableFiles/" 
 4) You can now run a test version of the webpage (index.php)
