import { useJwt } from 'react-jwt';

interface DecodeResult {
  success: boolean;
  message: string;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  data: any | null;
}

export const useDecodeToken = (token: string | null): DecodeResult => {
  const { decodedToken, isExpired } = useJwt(token || '');

  try {
    if (isExpired) {
      return {
        success: false,
        message: 'Is Expired Token',
        data: null,
      };
    }
    return {
      success: true,
      message: 'Token not expired',
      data: decodedToken,
    };
  } catch (error) {
    console.error('Decode token error:', error);
    return {
      success: false,
      message: 'Errors',
      data: null,
    };
  }
};
