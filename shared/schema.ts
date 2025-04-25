import { pgTable, text, serial, integer, decimal, boolean, timestamp, foreignKey, primaryKey, uniqueIndex, varchar } from "drizzle-orm/pg-core";
import { createInsertSchema } from "drizzle-zod";
import { z } from "zod";

// USERS table
export const users = pgTable("users", {
  id: serial("id").primaryKey(),
  name: varchar("name", { length: 100 }).notNull(),
  email: varchar("email", { length: 150 }).notNull().unique(),
  phone: varchar("phone", { length: 20 }),
  password: text("password").notNull(),
  address: text("address"),
  role: varchar("role", { length: 20 }).notNull().default("client"),
  can_give_discount: boolean("can_give_discount").default(false),
  created_at: timestamp("created_at").defaultNow()
});

// SERVICES table
export const services = pgTable("services", {
  id: serial("id").primaryKey(),
  name: varchar("name", { length: 100 }).notNull(),
  description: text("description"),
  price: decimal("price", { precision: 10, scale: 2 }).notNull()
});

// PACKAGES table
export const packages = pgTable("packages", {
  id: serial("id").primaryKey(),
  name: varchar("name", { length: 150 }).notNull(),
  image_url: text("image_url"),
  description: text("description"),
  price: decimal("price", { precision: 10, scale: 2 }),
  customized: boolean("customized").default(false),
  created_by: integer("created_by").references(() => users.id),
  created_at: timestamp("created_at").defaultNow()
});

// PACKAGE_SERVICES table
export const package_services = pgTable("package_services", {
  package_id: integer("package_id").references(() => packages.id, { onDelete: "cascade" }),
  service_id: integer("service_id").references(() => services.id, { onDelete: "cascade" })
}, (table) => ({
  pk: primaryKey({ columns: [table.package_id, table.service_id] })
}));

// BOOKINGS table
export const bookings = pgTable("bookings", {
  id: serial("id").primaryKey(),
  user_id: integer("user_id").references(() => users.id, { onDelete: "cascade" }),
  package_id: integer("package_id").references(() => packages.id),
  event_place: text("event_place").notNull(),
  event_date: timestamp("event_date").notNull(),
  discount: decimal("discount", { precision: 10, scale: 2 }),
  confirmed_by: integer("confirmed_by").references(() => users.id),
  status: varchar("status", { length: 20 }).default("pending"),
  created_at: timestamp("created_at").defaultNow()
});

// GUESTS table
export const guests = pgTable("guests", {
  id: serial("id").primaryKey(),
  booking_id: integer("booking_id").references(() => bookings.id, { onDelete: "cascade" }),
  name: varchar("name", { length: 100 }),
  email: varchar("email", { length: 150 }),
  phone: varchar("phone", { length: 20 }),
  rsvp_status: varchar("rsvp_status", { length: 10 }).default("pending")
});

// EVENT_ATTENDANCE table
export const event_attendance = pgTable("event_attendance", {
  id: serial("id").primaryKey(),
  guest_id: integer("guest_id").references(() => guests.id, { onDelete: "cascade" }),
  attended: boolean("attended").default(false),
  checked_in_at: timestamp("checked_in_at"),
  remarks: text("remarks")
});

// Insert Schemas
export const insertUserSchema = createInsertSchema(users).omit({ id: true, created_at: true });
export const insertServiceSchema = createInsertSchema(services).omit({ id: true });
export const insertPackageSchema = createInsertSchema(packages).omit({ id: true, created_at: true });
export const insertPackageServiceSchema = createInsertSchema(package_services);
export const insertBookingSchema = createInsertSchema(bookings).omit({ id: true, created_at: true, status: true, confirmed_by: true });
export const insertGuestSchema = createInsertSchema(guests).omit({ id: true, rsvp_status: true });
export const insertEventAttendanceSchema = createInsertSchema(event_attendance).omit({ id: true, attended: true, checked_in_at: true });

// Select Types
export type User = typeof users.$inferSelect;
export type Service = typeof services.$inferSelect;
export type Package = typeof packages.$inferSelect;
export type PackageService = typeof package_services.$inferSelect;
export type Booking = typeof bookings.$inferSelect;
export type Guest = typeof guests.$inferSelect;
export type EventAttendance = typeof event_attendance.$inferSelect;

// Insert Types
export type InsertUser = z.infer<typeof insertUserSchema>;
export type InsertService = z.infer<typeof insertServiceSchema>;
export type InsertPackage = z.infer<typeof insertPackageSchema>;
export type InsertPackageService = z.infer<typeof insertPackageServiceSchema>;
export type InsertBooking = z.infer<typeof insertBookingSchema>;
export type InsertGuest = z.infer<typeof insertGuestSchema>;
export type InsertEventAttendance = z.infer<typeof insertEventAttendanceSchema>;

// Registration Schema (extends insertUserSchema)
export const registrationSchema = insertUserSchema.extend({
  password: z.string().min(6, "Password must be at least 6 characters"),
  confirmPassword: z.string(),
}).refine((data) => data.password === data.confirmPassword, {
  message: "Passwords do not match",
  path: ["confirmPassword"],
});

// Login Schema
export const loginSchema = z.object({
  email: z.string().email("Invalid email address"),
  password: z.string().min(1, "Password is required"),
});

// Extended types for login
export type LoginData = z.infer<typeof loginSchema>;
export type RegistrationData = z.infer<typeof registrationSchema>;

// Package with services
export type PackageWithServices = Package & { services: Service[] };
export type BookingWithDetails = Booking & { package: Package, user: User, confirmed_by_user?: User };
export type GuestWithDetails = Guest & { booking: Booking };
