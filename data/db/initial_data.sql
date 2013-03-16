--
-- Dumping data for table `permission`
--

INSERT INTO `permission` (`id`, `code`, `description`) VALUES(1, 'read', NULL);
INSERT INTO `permission` (`id`, `code`, `description`) VALUES(2, 'delete', NULL);
INSERT INTO `permission` (`id`, `code`, `description`) VALUES(3, 'reuse', NULL);

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id`, `code`, `description`) VALUES(1, 'owner', NULL);
INSERT INTO `role` (`id`, `code`, `description`) VALUES(2, 'reuser', NULL);

--
-- Dumping data for table `role_has_permission`
--

INSERT INTO `role_has_permission` (`role_id`, `permission_id`) VALUES(1, 1);
INSERT INTO `role_has_permission` (`role_id`, `permission_id`) VALUES(2, 1);
INSERT INTO `role_has_permission` (`role_id`, `permission_id`) VALUES(1, 2);
INSERT INTO `role_has_permission` (`role_id`, `permission_id`) VALUES(1, 3);
INSERT INTO `role_has_permission` (`role_id`, `permission_id`) VALUES(2, 3);
