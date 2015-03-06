set -e

file=$1
out=$2

if [ "$file" == "" ]; then
    echo "Usage: 1to2.sh INFILE [OUTFILE]"
    exit 1;
fi
if [ "$out" == "" ]; then
    out=z2.yml
fi

if ! [ -e "$file" ]; then
    echo "File does not exist: $file"
    exit 2;
fi

cp $file $out

echo "Replacing 'set:' directives with 'args:'"
sed 's/set:/args:/g'                                                        -i $out;

echo "Replacing 'env:' global setting with 'envs:'"
sed 's/^env:/envs:/g'                                                       -i $out;

echo "Replacing 'env:' parameters with 'target_env:'"
sed 's/\(\s\)env:/\1target_env:/g'                                            -i $out;

echo "Replacing 'env.\*' expressions with 'envs[target_env].\*'"
sed 's/env\./envs[target_env]\./g'                  -i $out;

echo "Replacing 'env' expressions with 'target_env'"
sed 's/\$(env)/$(target_env)/g'                                             -i $out;

echo "Removing reference to core plugin"
sed 's/\(plugins:.*\)\('"'"'core'"'"', \?\|, \?'"'"'core'"'"'\)/\1/g'       -i $out;

echo "Replacing 'verbose' with 'VERBOSE'"
sed 's/\?(verbose)/?(VERBOSE)/g'                                          -i $out;

echo "Replacing ?(...) with @(if ...)"
sed 's/\?(/@(if /g'                                          -i $out;

echo "Done"
echo ""
echo "NOTE: Don't forget to add a version annotation at the top of the file, once you're satisfied"
