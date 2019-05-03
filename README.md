# tag_filter
Tag-Cloud like filtering in frontend

# USAGE
- Install extension
- Add a TagFilter field to your section
- In section's data-source, add the Tagfilter field as a Filters param (leave value empty)
- Now, in frontend you can control data-source filtering using 'tagaction' url parameter

## TAGACTION

Comma separated value of tag 'actions'. A tag-action is defined as `<operator>:<tag>`
Currently 3 operator are defined:
- `a`, add `<tag>` to filter list
- `r`, remove `<tag>` from filter list
- `c` (without parameter) clean filter list
  
