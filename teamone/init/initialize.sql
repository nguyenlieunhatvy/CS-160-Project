DROP TABLE IF EXISTS Userbase CASCADE;
CREATE TABLE Userbase (
  id SERIAL PRIMARY KEY,
  username VARCHAR(64) UNIQUE,
  password VARCHAR(256),
  firstName VARCHAR(32),
  lastName VARCHAR(32),
  email VARCHAR(64),
  since TIMESTAMP,
  lastLogin TIMESTAMP,
  ipAddr INET
);

DROP TABLE IF EXISTS Video;
CREATE TABLE Video (
  id SERIAL PRIMARY KEY,
  uploaderId INTEGER REFERENCES Userbase(id) ON DELETE CASCADE,
  title VARCHAR(64),
  uploadDate TIMESTAMP,
  fileSize INT,
  path VARCHAR(28),
  isProcessed BOOLEAN
);
