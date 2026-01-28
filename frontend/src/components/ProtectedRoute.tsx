import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
// import { useAuth } from '../hooks/useAuth';

interface ProtectedRouteProps {
  children: React.ReactNode;
  requiredRole?: 'admin' | 'manager_club' | 'peleador';
}

export default function ProtectedRoute({ children, requiredRole = 'admin' }: ProtectedRouteProps) {
  // TODO: Implement useAuth hook or equivalent authentication logic
  // const { user } = useAuth();
  const user = null as { id: number; nombre: string; tipo_nombre: string } | null;

  // Si no hay usuario, mostrar mensaje de no autorizado
  if (!user) {
    return (
      <View style={styles.container}>
        <View style={styles.errorBox}>
          <Text style={styles.errorIcon}>🔒</Text>
          <Text style={styles.errorTitle}>Acceso Restringido</Text>
          <Text style={styles.errorMessage}>
            Debes iniciar sesión para acceder a esta sección
          </Text>
          <TouchableOpacity style={styles.button}>
            <Text style={styles.buttonText}>Volver al Inicio</Text>
          </TouchableOpacity>
        </View>
      </View>
    );
  }

  // Verificar si el usuario tiene el rol requerido
  const hasPermission = checkPermission(user.tipo_nombre, requiredRole);

  if (!hasPermission) {
    return (
      <View style={styles.container}>
        <View style={styles.errorBox}>
          <Text style={styles.errorIcon}>⛔</Text>
          <Text style={styles.errorTitle}>Sin Permisos</Text>
          <Text style={styles.errorMessage}>
            No tienes permisos para acceder a esta sección.
            {'\n'}Rol requerido: {requiredRole.toUpperCase()}
          </Text>
          <Text style={styles.errorSubtext}>
            Tu rol actual: {user.tipo_nombre.toUpperCase()}
          </Text>
          <TouchableOpacity style={styles.button}>
            <Text style={styles.buttonText}>Volver al Inicio</Text>
          </TouchableOpacity>
        </View>
      </View>
    );
  }

  // Si tiene permisos, renderizar el contenido
  return <>{children}</>;
}

function checkPermission(userRole: string, requiredRole: string): boolean {
  // Admin tiene acceso a todo
  if (userRole === 'admin') return true;

  // Verificar rol específico
  return userRole === requiredRole;
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#1a1a1a',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  errorBox: {
    backgroundColor: '#2c2c2c',
    borderRadius: 20,
    padding: 40,
    alignItems: 'center',
    maxWidth: 400,
    borderWidth: 2,
    borderColor: '#e74c3c',
  },
  errorIcon: {
    fontSize: 80,
    marginBottom: 20,
  },
  errorTitle: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 15,
  },
  errorMessage: {
    fontSize: 16,
    color: '#ccc',
    textAlign: 'center',
    lineHeight: 24,
    marginBottom: 10,
  },
  errorSubtext: {
    fontSize: 14,
    color: '#999',
    textAlign: 'center',
    marginBottom: 30,
    fontStyle: 'italic',
  },
  button: {
    backgroundColor: '#e74c3c',
    paddingHorizontal: 30,
    paddingVertical: 15,
    borderRadius: 10,
  },
  buttonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
});
