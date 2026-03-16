use pothenesxes;

INSERT INTO users (user_id,first_name, last_name, email, phone, role)
Values ('1' , 'Admin' , 'Admin' , 'admin@test.com' , '00000000' , 'Admin'),
('2' , 'Paraskevas' , 'Vafeiadis' , 'paras@gmail.com' , '99004300' , 'User'),
('3' , 'Nikos' , 'Christodoulidis' , 'nikos@gmail.com' , '99999999' , 'Politician');

INSERT INTO accounts(user_id,username,password_hash)
VALUES('1' , 'admin' , 'admin123'),
('2' , 'test' , 'testing123'),
('3' , 'Nikaros' , 'anorthosis');

INSERT INTO positions(position_id,position_name)
VALUES ('1' , 'President'),
('2' , 'Member of Parliament'),
('3' , 'Member of European parliament');

INSERT INTO govOfficers(user_id , officer_position)
VALUES('3' , '1');