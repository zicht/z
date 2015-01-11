set -e

file=$1

if [ "$file" == "" ]; then
    echo "Usage: 1to2.sh FILE"
    exit 1;
fi
if ! [ -e "$file" ]; then
    echo "File does not exist: $file"
    exit 2;
fi

echo "Backing up $file to $file.1"
if [ -e "$file.1" ]; then
    echo "File exists! aborting";
    exit 3
fi

cp $file $file.1

echo "Replacing 'set:' directives with 'args:'"
sed 's/set:/args:/g'                                                        -i $file;

echo "Replacing 'env:' global setting with 'envs:'"
sed 's/^env:/envs:/g'                                                       -i $file;

echo "Replacing 'env:' parameters with 'target_env:'"
sed 's/\(\s\)env:/\1target_env:/g'                                            -i $file;

echo "Replacing 'env.\*' expressions with 'envs[target_env].\*'"
sed 's/env\./envs[target_env]\./g'                  -i $file;

echo "Replacing 'env' expressions with 'target_env'"
sed 's/\$(env)/$(target_env)/g'                                             -i $file;

echo "Removing reference to core plugin"
sed 's/\(plugins:.*\)\('"'"'core'"'"', \?\|, \?'"'"'core'"'"'\)/\1/g'       -i $file;

echo "Replacing 'verbose' with 'VERBOSE'"
sed 's/\?(verbose)/?(VERBOSE)/g'                                          -i $file;

echo "Replacing ?(...) with @(if ...)"
sed 's/\?(/@(if /g'                                          -i $file;

echo "Done"
echo ""
echo "NOTE: Don't forget to add a version annotation at the top of the file, once you're satisfied"
