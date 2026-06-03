import { updateAuthState } from "../utils/authEvents.js";

export const isDemoMode = import.meta.env.VITE_DEMO === "true";

const DEMO_TOKEN = "connect-in-demo-token";
const USERS_KEY = "connectin_demo_users";
const POSTS_KEY = "connectin_demo_posts";

const demoUsers = [
	{
		id: 1,
		username: "morgan.demo",
		first_name: "Morgan",
		last_name: "Rehel",
		email: "morgan.demo@connectin.local",
		bio: "Developpeur web en formation, cote front comme cote API. Cette session est une demo portfolio.",
		profile_photo_path: null,
		cover_image_path: null,
		last_seen_at: new Date().toISOString(),
	},
	{
		id: 2,
		username: "lea.product",
		first_name: "Lea",
		last_name: "Martin",
		email: "lea.martin@connectin.local",
		bio: "Product owner, fan de specs claires et de retours utilisateurs rapides.",
		profile_photo_path: null,
		cover_image_path: null,
		last_seen_at: new Date().toISOString(),
	},
	{
		id: 3,
		username: "nora.design",
		first_name: "Nora",
		last_name: "Benali",
		email: "nora.benali@connectin.local",
		bio: "UI designer. J'aime les interfaces lisibles, accessibles et faciles a scanner.",
		profile_photo_path: null,
		cover_image_path: null,
		last_seen_at: "2026-06-03T07:35:00.000Z",
	},
	{
		id: 4,
		username: "alex.api",
		first_name: "Alex",
		last_name: "Durand",
		email: "alex.durand@connectin.local",
		bio: "Backend Laravel, securite et documentation d'API.",
		profile_photo_path: null,
		cover_image_path: null,
		last_seen_at: "2026-06-02T15:15:00.000Z",
	},
];

const demoPosts = [
	{
		id: 101,
		user_id: 2,
		content:
			"Le prototype du fil d'actualite est pret pour la revue d'equipe. Les reactions, commentaires et profils sont maintenant consultables dans la demo.",
		created_at: "2026-06-03T08:50:00.000Z",
		formatted_date: "12 min",
		likes: [{ user_id: 1 }, { user_id: 3 }, { user_id: 4 }],
		comments: [
			{
				id: 501,
				user_id: 1,
				content: "Top, je vais verifier le parcours profil et la page detail.",
				created_at: "2026-06-03T09:02:00.000Z",
			},
			{
				id: 502,
				user_id: 3,
				content: "La hierarchie visuelle est plus claire, surtout sur mobile.",
				created_at: "2026-06-03T09:08:00.000Z",
			},
		],
		media: [],
	},
	{
		id: 102,
		user_id: 1,
		content:
			"Objectif de la journee : rendre l'interface visitable sans backend pour le portfolio GitHub Pages, tout en gardant l'application connectee au backend en local.",
		created_at: "2026-06-03T07:40:00.000Z",
		formatted_date: "1 h",
		likes: [{ user_id: 2 }, { user_id: 3 }],
		comments: [
			{
				id: 503,
				user_id: 4,
				content: "Bonne approche : un mode demo separe evite de modifier le comportement de prod.",
				created_at: "2026-06-03T08:10:00.000Z",
			},
		],
		media: [],
	},
	{
		id: 103,
		user_id: 3,
		content:
			"Petit rappel design : les boutons d'action doivent rester visibles au clavier et les cartes doivent garder assez d'espace pour les longs textes.",
		created_at: "2026-06-02T16:15:00.000Z",
		formatted_date: "hier",
		likes: [{ user_id: 1 }, { user_id: 2 }, { user_id: 4 }],
		comments: [],
		media: [],
	},
	{
		id: 104,
		user_id: 4,
		content:
			"Le backend expose deja les routes posts, likes, commentaires et profils. La demo statique simule ces retours pour que les visiteurs puissent explorer l'UI.",
		created_at: "2026-06-02T14:20:00.000Z",
		formatted_date: "hier",
		likes: [{ user_id: 2 }],
		comments: [
			{
				id: 504,
				user_id: 1,
				content: "Parfait pour montrer le projet sans demander d'installation.",
				created_at: "2026-06-02T15:00:00.000Z",
			},
		],
		media: [],
	},
];

const clone = (value) => JSON.parse(JSON.stringify(value));

const read = (key, fallback) => {
	const stored = localStorage.getItem(key);
	if (!stored) return clone(fallback);
	try {
		return JSON.parse(stored);
	} catch {
		localStorage.removeItem(key);
		return clone(fallback);
	}
};

const write = (key, value) => {
	localStorage.setItem(key, JSON.stringify(value));
};

const getUsers = () => read(USERS_KEY, demoUsers);
const saveUsers = (users) => write(USERS_KEY, users);
const getPosts = () => read(POSTS_KEY, demoPosts);
const savePosts = (posts) => write(POSTS_KEY, posts);

const currentUser = () => JSON.parse(localStorage.getItem("user") || "null");

const enrichComment = (comment, users) => ({
	...comment,
	user: users.find((user) => Number(user.id) === Number(comment.user_id)) || null,
});

const enrichPost = (post, users) => {
	const user = users.find((item) => Number(item.id) === Number(post.user_id)) || null;
	const comments = (post.comments || []).map((comment) => enrichComment(comment, users));
	const loggedUser = currentUser();
	const likes = post.likes || [];

	return {
		...post,
		user,
		comments,
		comments_count: comments.length,
		likes,
		likes_count: likes.length,
		is_liked: loggedUser ? likes.some((like) => Number(like.user_id) === Number(loggedUser.id)) : false,
	};
};

export const demoStore = {
	startSession(userData = demoUsers[0]) {
		const users = getUsers();
		const user = users.find((item) => Number(item.id) === Number(userData.id)) || users[0];
		localStorage.setItem("token", DEMO_TOKEN);
		localStorage.setItem("user", JSON.stringify(user));
		updateAuthState();
		return { token: DEMO_TOKEN, user };
	},

	login() {
		return this.startSession();
	},

	register(userData) {
		const users = getUsers();
		const user = {
			id: Math.max(...users.map((item) => item.id)) + 1,
			username: userData.username || "nouveau.demo",
			first_name: userData.first_name || "Nouveau",
			last_name: userData.last_name || "Membre",
			email: userData.email || "nouveau@connectin.local",
			bio: "Compte cree dans la demo portfolio.",
			profile_photo_path: null,
			cover_image_path: null,
			last_seen_at: new Date().toISOString(),
		};
		users.push(user);
		saveUsers(users);
		return this.startSession(user);
	},

	logout() {
		localStorage.removeItem("token");
		localStorage.removeItem("user");
		updateAuthState();
		return { message: "Session demo fermee." };
	},

	getProfile() {
		if (!localStorage.getItem("token")) return null;
		const users = getUsers();
		const storedUser = currentUser();
		const user = users.find((item) => Number(item.id) === Number(storedUser?.id)) || users[0];
		localStorage.setItem("user", JSON.stringify(user));
		updateAuthState();
		return user;
	},

	updateProfile(userId, { bio, deleteCover, deleteProfilePhoto }) {
		const users = getUsers();
		const index = users.findIndex((user) => Number(user.id) === Number(userId));
		if (index === -1) throw new Error("Utilisateur introuvable");

		users[index] = {
			...users[index],
			bio: bio ?? users[index].bio,
			cover_image_path: deleteCover ? null : users[index].cover_image_path,
			profile_photo_path: deleteProfilePhoto ? null : users[index].profile_photo_path,
			last_seen_at: new Date().toISOString(),
		};

		saveUsers(users);
		localStorage.setItem("user", JSON.stringify(users[index]));
		updateAuthState();
		return users[index];
	},

	updateAccount(userId, payload) {
		const users = getUsers();
		const index = users.findIndex((user) => Number(user.id) === Number(userId));
		if (index === -1) throw new Error("Utilisateur introuvable");

		users[index] = {
			...users[index],
			username: payload.username || users[index].username,
			email: payload.email || users[index].email,
			last_seen_at: new Date().toISOString(),
		};

		saveUsers(users);
		localStorage.setItem("user", JSON.stringify(users[index]));
		updateAuthState();
		return { user: users[index] };
	},

	deleteAccount() {
		this.logout();
		return { message: "Compte demo reinitialise pour la session." };
	},

	getAllPosts() {
		const users = getUsers();
		return getPosts()
			.map((post) => enrichPost(post, users))
			.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
	},

	getPostById(id) {
		const users = getUsers();
		const post = getPosts().find((item) => Number(item.id) === Number(id));
		if (!post) throw new Error("Publication introuvable");
		return enrichPost(post, users);
	},

	createPost(data) {
		const user = currentUser();
		if (!user) throw new Error("Non authentifie");

		const posts = getPosts();
		const post = {
			id: Math.max(...posts.map((item) => item.id)) + 1,
			user_id: user.id,
			content: data.get?.("content") || data.content || "",
			created_at: new Date().toISOString(),
			formatted_date: "a l'instant",
			likes: [],
			comments: [],
			media: [],
		};

		posts.unshift(post);
		savePosts(posts);
		return enrichPost(post, getUsers());
	},

	updatePost(id, content) {
		const posts = getPosts();
		const index = posts.findIndex((post) => Number(post.id) === Number(id));
		if (index === -1) throw new Error("Publication introuvable");
		posts[index] = { ...posts[index], content };
		savePosts(posts);
		return enrichPost(posts[index], getUsers());
	},

	deletePost(id) {
		savePosts(getPosts().filter((post) => Number(post.id) !== Number(id)));
		return { message: "Publication supprimee." };
	},

	toggleLike(postId) {
		const user = currentUser();
		if (!user) throw new Error("Non authentifie");

		const posts = getPosts();
		const index = posts.findIndex((post) => Number(post.id) === Number(postId));
		if (index === -1) throw new Error("Publication introuvable");

		const likes = posts[index].likes || [];
		const alreadyLiked = likes.some((like) => Number(like.user_id) === Number(user.id));
		posts[index].likes = alreadyLiked
			? likes.filter((like) => Number(like.user_id) !== Number(user.id))
			: [...likes, { user_id: user.id }];

		savePosts(posts);
		return {
			is_liked: !alreadyLiked,
			likes_count: posts[index].likes.length,
		};
	},

	addComment(postId, content) {
		const user = currentUser();
		if (!user) throw new Error("Non authentifie");

		const posts = getPosts();
		const index = posts.findIndex((post) => Number(post.id) === Number(postId));
		if (index === -1) throw new Error("Publication introuvable");

		const comments = posts.flatMap((post) => post.comments || []);
		const comment = {
			id: Math.max(...comments.map((item) => item.id), 500) + 1,
			user_id: user.id,
			content,
			created_at: new Date().toISOString(),
		};

		posts[index].comments = [...(posts[index].comments || []), comment];
		savePosts(posts);
		return enrichComment(comment, getUsers());
	},

	updateComment(commentId, content) {
		const posts = getPosts();
		for (const post of posts) {
			const comment = (post.comments || []).find((item) => Number(item.id) === Number(commentId));
			if (comment) {
				comment.content = content;
				savePosts(posts);
				return enrichComment(comment, getUsers());
			}
		}
		throw new Error("Commentaire introuvable");
	},

	deleteComment(commentId) {
		const posts = getPosts().map((post) => ({
			...post,
			comments: (post.comments || []).filter((comment) => Number(comment.id) !== Number(commentId)),
		}));
		savePosts(posts);
		return { message: "Commentaire supprime." };
	},

	getUserById(id) {
		const user = getUsers().find((item) => Number(item.id) === Number(id));
		if (!user) throw new Error("Utilisateur introuvable");
		return user;
	},

	getPostsByUser(userId) {
		const users = getUsers();
		return getPosts()
			.filter((post) => Number(post.user_id) === Number(userId))
			.map((post) => enrichPost(post, users))
			.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
	},
};
