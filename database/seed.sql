use pothenesxes;

INSERT INTO positions(position_id,position_name)
VALUES 
('1' , 'President'),
('2' , 'Member of Parliament'),
('3' , 'Member of European parliament'),
('4' , 'Minister');

INSERT INTO users (first_name, last_name, email, phone,position_id, role)
Values ( 'Admin' , 'Admin' , 'admin@test.com' , '00000000' , '999' , 'Admin'),
('Paraskevas' , 'Vafeiadis' , 'paras@gmail.com' , '99004300' , '999' , 'User'),
('Nikos' , 'Christodoulidis' , 'nikos@gmail.com' , '99999999' , '1' , 'Politician'),
('Feidias' , 'Panayiotou' , 'fpana@gmail.com' , '99887766' , '3' , 'Politician'),
('Alexis' , 'Vafeadis' , 'aleks@gmail.com' , '99776655' , '4' , 'Politician');

INSERT INTO accounts(user_id,username,password_hash)
VALUES('1' , 'admin' , '$2a$12$5KDPQ8zFGGIJEe8Ba9pu.uMsuGbiA0qcrN8t8HhvHLr/YArlHv64S'), /*password is "admin123"*/
('2' , 'test' , '$2a$12$A04wG/KZVZRhFQ5kYoD3SOpaI/2GET.dhKB73Cgr0hwLOGi2Nj7iq'),/*password is "testing123"*/
('3' , 'Nikaros' , '$2a$12$3ruv8Y.0WbfGYbTulhbTLuiRzzWGlM2p7UPhiT/tglribZBjIPH8m'), /*password is "pafos1234"*/
('4' , 'Fpana' , '$2y$10$u6PoAYJkTznzt7Gw62rItejoRzLcQqFVsPMlqCgjXStBsQDSKjbyy'), /*password is "feidias12"*/
('5' , 'Aleks' , '$2a$12$bHbrT/87pWLD8hn2b4xoTuTppdWlaw5DHgHkG3jB.z4BUgdbbrVy6'); /*password is "Minister123"*/