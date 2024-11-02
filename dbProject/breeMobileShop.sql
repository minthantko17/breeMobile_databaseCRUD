create database breeMobileShop;
use breeMobileShop;

create table BRANCHES(
	branchId INT(2) NOT NULL,
    branchName VARCHAR(50),
    mobileNumber BIGINT(10),
    email VARCHAR(50),
    openingHour TIME DEFAULT '09:00:00',
    closingHour TIME DEFAULT '20:00:00',
    street VARCHAR(25),
    city VARCHAR(25),
    state VARCHAR(25),
    postalCode INT(5),
    constraint BRANCHPK primary key(branchId)
);

create table EMPLOYEES(
	employeeId INT(9) NOT NULL,
    branchId INT(2) NOT NULL,
    firstName VARCHAR(25),
    lastName VARCHAR(25) NOT NULL,
    empRole VARCHAR(25) default "staff",
    dateEmployed DATETIME default current_timestamp,
    salary INT(10),
    mobileNumber BIGINT(10),
    email VARCHAR(50),
    street VARCHAR(25),
    city VARCHAR(25),
    state VARCHAR(25),
    postalCode INT(5),
    constraint EMPLOYEEPK primary key(employeeId),
    constraint EMPLOYEEFK foreign key(branchId) references BRANCHES(branchId)    
);

create table SUPPLIERS(
	supplierId INT(5) NOT NULL,
    supplierName VARCHAR(50) default "Default Supplier",
    mobileNumber BIGINT(10),
    email VARCHAR(50),
    street VARCHAR(25),
    city VARCHAR(25),
    state VARCHAR(25),
    postalCode INT(5),    
    constraint SUPPLIERPK primary key(supplierId)
);

create table PRODUCTS(
	productId INT(6) NOT NULL,
    productName VARCHAR(50),
    productType ENUM('P','A') NOT NULL,
	price DECIMAL(10,2),
    quantity INT(3),
    model VARCHAR(25),
    color VARCHAR(50),
    supplierId INT(5),
    constraint PRODUCTPK primary key(productId),
    constraint PRODUCTFK foreign key(supplierId) references SUPPLIERS(supplierId)
    ON DELETE SET NULL 
    ON UPDATE CASCADE
);

create table PHONES(
	productId INT(6) NOT NULL,
    osType VARCHAR(10),
    phoneStorage INT(4),
    phoneMemory INT(2),
    batteryCapacity INT(5),
	resolution VARCHAR(9),
    refreshRate INT(3),
    constraint PHONEPK primary key(productId),
    constraint PHONEFK foreign key(productId) references PRODUCTS(productId)
    ON DELETE CASCADE
	ON UPDATE CASCADE
);

create table ACCESSORIES(
	productId INT(6) NOT NULL,
    compatibleDeviceId INT(6),
    accessoryType ENUM('CV', 'CH', 'H') NOT NULL,
    constraint ACCESSORYPK primary key (productId),
    constraint ACCESSORYFK1 foreign key(productId) references PRODUCTS(productId)
	ON DELETE CASCADE
	ON UPDATE CASCADE,
    constraint ACCESSORYFK2 foreign key(compatibleDeviceId) references PHONES(productId) 
	ON DELETE SET NULL
	ON UPDATE CASCADE
);

create table COVERS(
	productId INT(6) NOT NULL,
    texture VARCHAR(25),
    constraint COVERPK primary key (productId),
    constraint COVERFK foreign key(productId) references ACCESSORIES(productId)    
    ON DELETE CASCADE
	ON UPDATE CASCADE
);

create table CHARGERS(
	productId INT(6) NOT NULL,
    outputWatt DECIMAL(4,1),
    constraint CHARGERPK primary key (productId),
    constraint CHARGERFK foreign key(productId) references ACCESSORIES(productId)    
    ON DELETE CASCADE
	ON UPDATE CASCADE
);

create table HEADSETS(
	productId INT(6) NOT NULL,
    headsetType VARCHAR(25),
    soundQuality INT(5),
    constraint HEADSETPK primary key (productId),
    constraint HEADSETFK foreign key(productId) references ACCESSORIES(productId)    
    ON DELETE CASCADE
	ON UPDATE CASCADE
);

create table IMPORTDETAILS(
	importDetailId INT(6) NOT NULL,
    branchId INT(2) NOT NULL,
    productId INT(6) NOT NULL,
    importDateTime DATETIME DEFAULT NOW(),
    constraint IMPORTDETAILPK primary key(importDetailId),
    constraint IMPORTDETAILFK1 foreign key(branchId) references BRANCHES(branchId)    
    ON DELETE CASCADE
	ON UPDATE CASCADE,
    constraint IMPORTDETAILFK2 foreign key(productId) references PRODUCTS(productId)    
    ON DELETE CASCADE
	ON UPDATE CASCADE
);

create table CUSTOMERS(
	customerId INT(9) NOT NULL,
    firstName VARCHAR(25),
    lastName VARCHAR(25) NOT NULL,
	memberPoint INT,
    mobileNumber BIGINT(10),
    email VARCHAR(50),
    street VARCHAR(25),
    city VARCHAR(25),
    state VARCHAR(25),
    postalCode INT(5),
    constraint CUSTOMERPK primary key(customerId)   
);

create table WARRANTIES(
	warrantyId INT(6) NOT NULL,
    claimDate DATETIME DEFAULT NOW(),
    validDurationMonth INT(2) DEFAULT 1,
    isClaimed BOOLEAN default 0,
    customerId INT(9),
    productId INT(6),
    constraint WARRANTYPK primary key(warrantyId),
    constraint WARRANTYFK1 foreign key(customerId) references CUSTOMERS(customerId)    
    ON DELETE SET NULL
	ON UPDATE CASCADE,
    constraint WARRANTYFK2 foreign key(productId) references PRODUCTS(productId)
	ON DELETE SET NULL
	ON UPDATE CASCADE
);

create table PROMOTIONS(
	promotionId INT(6) NOT NULL,
    promotionName VARCHAR(25),
    startDate DATE,
    endDate DATE,
	constraint PROMOTIONPK primary key(promotionId)
);

create table PROMOTIONPRODUCTS(
	promotionProductId	INT(6) NOT NULL,
    productId INT(6) NOT NULL,
    promotionId INT NOT NULL,
    promotionAmount DECIMAL(10,2) DEFAULT 0,
    constraint PROMOTIONPRODUCTPK primary key(promotionProductId),
    constraint PROMOTIONPRODUCTFK1 foreign key(productId) references PRODUCTS(productId)    
    ON DELETE CASCADE
	ON UPDATE CASCADE,
    constraint PROMOTIONPRODUCTFK2 foreign key(promotionId) references PROMOTIONS(promotionId)    
    ON DELETE CASCADE
	ON UPDATE CASCADE
);


create table SHIPMENTS(
	shipmentId INT(6) NOT NULL,
    shipmentStatus VARCHAR(25),
    shipmentDate DATETIME,
    shipmentMethod VARCHAR(25),
    shipmentCost DEC(6, 2),
    customerId INT(9) NOT NULL,
    trackingNumber varchar(25),
    constraint SHIPMENTPK primary key(shipmentId),
    constraint SHIPMENTFK foreign key(customerId) references CUSTOMERS(customerId)
	ON DELETE CASCADE
	ON UPDATE CASCADE
);


create table ORDERS(
	orderId INT(9) NOT NULL,
    totalAmount DEC(10,2) NOT NULL,
    orderDate DATETIME DEFAULT NOW(),
    customerId INT(9) NOT NULL,
    shipmentId INT(9) NULL,
    constraint ORDERSPK primary key(orderId),
    constraint ORDERSFK1 foreign key(customerId) references CUSTOMERS(customerId)
	ON DELETE CASCADE
	ON UPDATE CASCADE,
    constraint ORDERSFK2 foreign key(shipmentId) references SHIPMENTS(shipmentId)
	ON DELETE SET NULL
	ON UPDATE CASCADE
);

create table ORDERBRANCHES(
	orderBranchId INT(6) NOT NULL,
    orderId INT(9) NOT NULL,
    branchId INT(2) NOT NULL,
    constraint ORDERBRANCHPK primary key(orderBranchId),
    constraint ORDERBRANCHFK1 foreign key(orderId) references ORDERS(orderId)
	ON DELETE CASCADE
	ON UPDATE CASCADE,
    constraint ORDERBRANCHFK2 foreign key(branchId) references BRANCHES(branchId)
	ON DELETE CASCADE
	ON UPDATE CASCADE
);

create table ORDERDETAILS(
	orderId INT(9) NOT NULL,
    productId INT(6) NOT NULL,
    quantity INT(6) NOT NULL,
    totalPrice INT(9) NOT NULL,
    constraint ORDERDETAILSPK primary key(orderId, productId),
    constraint ORDERDETAILSFK1 foreign key(orderId) references ORDERS(orderId)
	ON DELETE CASCADE
	ON UPDATE CASCADE,
    constraint ORDERDETAILSFK2 foreign key(productId) references PRODUCTS(productId)
	ON DELETE CASCADE
	ON UPDATE CASCADE
);











