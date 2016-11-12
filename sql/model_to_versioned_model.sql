--
-- Moving a Modular\Model to a Modular\VersionedModel means we need to copy the existing data for the model into
-- the VersionedModel table. ATM this script only deals with moving a single `Modular\Model` to `Modular\VersionedModel`.
--
-- Important! Make sure you have a backup of the DB before you do this!
--
-- Run first time a VersionedModel is used:
--

create table `Modular\VersionedModel` select * from `Modular\Model` where ClassName = '';

alter table `Modular\VersionedModel` modify column ID int not null auto_increment primary key;
alter table `Modular\VersionedModel` add index ClassName (ClassName);

-- If `Modular\VersionedModel` already exists then:

insert into `Modular\VersionedModel` select * from `Modular\Model` where ClassName = '';
alter table `Modular\VersionedModel` modify column ID int not null auto_increment primary key;
alter table `Modular\VersionedModel` add index ClassName (ClassName);

-- Now change the Model inheritance from `Modular\Model` to `Modular\VersionedModel` and run /dev/build?flush=1

-- Turn off default blocks (run on all classes that have the 'Modular\Extensions\Views\AddDefaultBlocks' extension)
-- e.g. Page and GridListBlock

update Page set AddDefaultBlocks = 0;
update GridListBlock set AddDefaultBlocks = 0;

-- Make sure you have added and configured `namespace Modular\Workflows\ModelExtension` to models which have become
-- VersionedModels

-- Publish all pages on the site:  /admin/pages/publishall

-- Job Done!



