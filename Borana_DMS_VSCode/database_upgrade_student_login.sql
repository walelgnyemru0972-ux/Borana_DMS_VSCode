USE borana_dms;

ALTER TABLE students
  ADD COLUMN password_hash VARCHAR(255) NULL AFTER department;
