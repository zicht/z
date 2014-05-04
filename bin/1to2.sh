file=$1

echo "Backing up $file to $file.1"
if [ -e "$file.1" ]; then
    echo "File exists! aborting";
    exit 1
fi

cp $file $file.1

echo "Replacing 'set:' directives with 'args:'"
sed 's/set:/args:/g'                                                        -i $file;

echo "Replacing 'env:' parameters with 'target_env:'"
sed 's/\benv:/target_env:/g'                                                -i $file;

echo "Replacing 'env.*' expressions with 'envs[target_env].*'"
sed 's/env\.\(ssh\|web\|root\|db\)/envs[target_env]\.\1/g'                  -i $file;

echo "Replacing 'env' expressions with 'target_env'"
sed 's/\$(env)/$(target_env)/g'                                             -i $file;

echo "Removing reference to core plugin"
sed 's/\(plugins:.*\)\('"'"'core'"'"', \?\|, \?'"'"'core'"'"'\)/\1/g'       -i $file;

echo "Done"

echo "Don't forget to add a version annotation at the top of the file, once you're satisfied"
