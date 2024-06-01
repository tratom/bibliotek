

// Validate banned field if it exists
        $banned = isset($params['banned']) ? ($params['banned'] === 'on') : false;

        // Validate admin field if it exists
        $admin = isset($params['admin']) ? ($params['admin'] === 'on') : false;

        $user = new EntityUser();
        $user->setName(filter_var($params['name'], FILTER_UNSAFE_RAW));
        $user->setSurname(filter_var($params['surname'], FILTER_UNSAFE_RAW));
        $user->setEmail(filter_var($params['email'], FILTER_SANITIZE_EMAIL));
        $user->setMaxLoanNum((int) $params['maxLoanNum']);
        $user->setMaxLoanDuration((int) $params['maxLoanDuration']);
        $user->setReputation((int) $params['reputation']);
        $user->setBirthday(new \DateTime($params['birthday']));
        $user->setBanned($banned);

        if (empty($params['newPassword'])) {
            $GLOBALS['msg']->error("Password is required");
            return new RedirectResponse('/admin/users/add', 302);
        }
        $user->setPassword(password_hash($params['newPassword'], PASSWORD_BCRYPT));

        // Handle admin role if applicable
        if ($admin) {
            $user->setRole('admin');
        } else {
            $user->setRole('user');
        }

        $GLOBALS['entityManager']->persist($user);
        $GLOBALS['entityManager']->flush();

        $GLOBALS['msg']->success('The user was successfully created.');
        return new RedirectResponse('/admin/users', 302);
    }
}