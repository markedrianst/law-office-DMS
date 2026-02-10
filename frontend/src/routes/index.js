import { createRouter, createWebHistory } from "vue-router";

// Pages
import Login from "@/pages/Authentication/Login.vue";
import Register from "@/pages/Authentication/Register.vue";
// Lawyer pages
import LawyerDashboard from "@/pages/Lawyer/Lawyerdashboard.vue";

// Staff pages
import StaffDashboard from "@/pages/Staff/Staffdashboard.vue";
import Clients from "@/pages/Staff/Clients.vue";
import Settings from "@/pages/Staff/Setting.vue";

// Admin pages
import AdminDashboard from "@/pages/Admin/Admindashboard.vue";
import ManageUser from "@/pages/Admin/ManageUser.vue";
import ActivityLogs from "@/pages/Admin/ActivityLogs.vue";



import NotFound from "@/Error/NotFound.vue";

const routes = [
  //auth routes
  { path: "/", component: Login },
  { path: "/register", component: Register },
//admin routes  
  { path: "/manageusers", component: ManageUser, meta: { requiresAuth: true, role: "admin" } },
  { path: "/admindashboard", component: AdminDashboard, meta: { requiresAuth: true, role: "admin" } },
  { path: "/activitylogs", component: ActivityLogs, meta: { requiresAuth: true, role: "admin" } },
  
  //staff routes
  { path: "/staffdashboard", component: StaffDashboard, meta: { requiresAuth: true, role: "staff" } },
  { path: "/clients", component: Clients, meta: { requiresAuth: true, role: "staff" } },
  { path: "/settings", component: Settings, meta: { requiresAuth: true, role: "staff" } },
  
  //lawyer routes
  { path: "/lawyerdashboard", component: LawyerDashboard, meta: { requiresAuth: true, role: "lawyer" } },
  { 
    path: "/:pathMatch(.*)*", 
    name: "NotFound",
    component: NotFound
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});


router.beforeEach((to, from, next) => {
  const token = localStorage.getItem("token");
  const user = JSON.parse(localStorage.getItem("user") || "{}");

  // If trying to access 404 page, allow it
  if (to.name === "NotFound") return next();

  // If route requires auth but no token - SHOW 404
  if (to.meta.requiresAuth && !token) {
    return next({ name: "NotFound" });
  }

  if (to.meta.requiresAuth && to.meta.role && user.role !== to.meta.role) {
    return next({ name: "NotFound" });
  }

  if ((to.path === "/" || to.path === "/register") && token) {
    if (user.role === "admin") return next("/admindashboard");
    if (user.role === "staff") return next("/staffdashboard");
    if (user.role === "lawyer") return next("/lawyerdashboard");
  }

  next();
});

export default router;