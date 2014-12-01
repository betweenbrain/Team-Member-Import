## Team Member Import

A Joomla CLI script for importing a CSV file as a set of Joomla articles, used as Team Member pages for [Starberry](http://starberry.tv/).

### Usage

Clone this repo, or manually add `import-team.php`, to your Joomla site's `cli` directory.

Used as `php import-team.php --file team.csv --parentId 11 --fieldsMap fields.csv`

Where
 
 * `--file` [required] designates the CSV file containing data to import.
 * `--parentId` [optional] designates the category parent ID to use when looking up a category ID based on the category alias.
 * `fieldsMap` [optional, requiored for FieldsAttach] used to designate a fields mapping file to define the field IDs and the CSV column names they correspond to.
 
### Fields Mapping File
Is a simple two column file with the first row having column names of `fieldid` and `column`. Subsequent rows to contain the related data. Such as:
 
    fieldId,column
    1,office
    2,name
    3,jobtitle
    
Will add the data from the `office` column, of the CSV file, such that it is associated with the article as a FieldsAttach field having an ID of 1. 