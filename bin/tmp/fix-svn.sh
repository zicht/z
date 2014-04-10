repo=$1
reposrc=$1/z-plugins/trunk
repotgt=$1/z-plugin-

for plugin in $(svn ls $reposrc | egrep '/$' | sed 's!/$!!'); do
    echo svn mkdir $repotgt$plugin
    echo svn mv $reposrc/$plugin $repotgt$plugin/trunk;
    echo svn mkdir $repotgt$plugin/{tags,branches}
done;
