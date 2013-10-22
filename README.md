bkp
===

Backup tool for MongoDB on debian linux server


{
  "_id":"testdb"
  "allowed": true,
   "autoremove": {
     "count": 3, // You can store max 3 dumps
     "days": 60 // Max lifetime of backup is 60 days
  },
   "rules": {
     "mm": "*", // Time of
     "hh": "*", // backup.
     "d": "*",  // Date
     "m": "*",  // of
     "y": 2013, // backup
     "dd": "2"  // day week.
  }
}