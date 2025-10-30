 import 'package:flutter/material.dart';
 import 'package:provider/provider.dart';
 import '../providers/auth_provider.dart';

 class HomeScreen extends StatelessWidget {
   const HomeScreen({super.key});

   @override
   Widget build(BuildContext context) {
     return Scaffold(
       appBar: AppBar(
         title: const Text('Home'),
         actions: [
           IconButton(
             icon: const Icon(Icons.logout),
             tooltip: 'Logout',
             onPressed: () async {
               await context.read<AuthProvider>().logout();
               if (context.mounted) {
                 Navigator.of(context).pushReplacementNamed('/login');
               }
             },
           )
         ],
       ),
       body: Center(
         child: Column(
           mainAxisSize: MainAxisSize.min,
           children: [
             const Icon(Icons.check_circle, size: 64, color: Colors.green),
             const SizedBox(height: 12),
             Text(
               'Login sukses! Selamat datang 👋',
               style: Theme.of(context).textTheme.titleMedium,
             ),
           ],
         ),
       ),
     );
   }
 }
