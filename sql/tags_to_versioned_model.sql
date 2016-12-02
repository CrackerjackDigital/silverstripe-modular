# 1.	change Modular\Model\Tag to inherit from Modular\Models\VersionedModel instead of Modular\Models\Model
# 2.	run /dev/build
# 3.	run:
insert into `Modular\Models\Tag_Live`;

delete from `Modular\VersionedModel` where ID in (select ID from `Modular\Models\Tag`);
delete from `Modular\VersionedModel_Live` where ID in (select ID from `Modular\Models\Tag`);
delete from `Modular\VersionedModel_versions` where RecordID in (select ID from `Modular\Models\Tag`);

insert into `Modular\VersionedModel` (ID, ClassName, Created, LastEdited, Version) select ID, 'Modular\\Models\\Tag', now(), now(), 0 from `Modular\Models\Tag`;
insert into `Modular\VersionedModel_Live` (ID, ClassName, Created, LastEdited, Version) select ID, 'Modular\\Models\\Tag', now(), now(), 0 from `Modular\Models\Tag`;
