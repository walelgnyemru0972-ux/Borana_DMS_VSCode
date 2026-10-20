USE borana_dms;

ALTER TABLE rooms
  DROP CONSTRAINT chk_room_capacity,
  ADD CONSTRAINT chk_room_capacity CHECK (capacity BETWEEN 1 AND 6);
