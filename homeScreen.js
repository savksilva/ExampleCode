import React, { Component } from 'react'
import { ScrollView, Text, Image, View, FlatList, TouchableHighlight, ActivityIndicator } from 'react-native'
import firebase from 'react-native-firebase'
import NavBarRegular from '../../Components/NavBarRegular'
import styles from './Styles/HomeScreenStyles'
import Icon from 'react-native-vector-icons/MaterialIcons'
import { Colors, Metrics } from '../../Themes'
import { connect } from 'react-redux'
import MallActions from '../../Redux/MallRedux'

const k = 0;

class HomeScreen extends Component {
  constructor(props) {
    super(props);
    this.state = {
      mallsList: [],
      isLoading: true,
      message:false
    }
    this.malls = null;
  }

  componentWillMount() {
    this.malls = firebase.database().ref("malls");
    this.malls.on("value", this.handleMallsList);
  }

  componentWillUnMount() {
    if (this.malls) {
      this.malls.off("value", this.handleMallsList);
    }
  }

  handleMallsList = (malls) => {
    var data = malls.val(), result = [],bool=false, temp = {}, cont = 0;
    for (var i in data) {
      temp[i] = data[i];
      temp[i].uid = i;
      cont++;
      if (cont % 2 == 0) {
        result.push(temp);
        temp = {}
      }
    }
    if(cont==0){
      bool= true;
    }
    result.push(temp);
    temp = {}

    this.setState({ message:bool,mallsList: result, isLoading: false });
  }

  openDrawer = () => {
    this.props.navigation.navigate("DrawerOpen");
  }

  openStores = (uid) => {
    this.props.setMall(uid);
    this.props.navigation.navigate("StoresScreen",{route: null, subRoute: null});
  }

  _keyExtractor = () => k++;

  _renderItem = ({ item }) => {

    return (
      <View style={styles.mallsRow}>
        {Object.keys(item).map((i) => {
          return (
            <TouchableHighlight key={i} onPress={() => { this.openStores(item[i].uid) }}>
              <View>
                <Image style={[{ padding: 5 }, styles.mallImage]} source={{isStatic:true, uri: item[i].logo, cache: 'force-cache'  }} />
                <Text style={styles.mallName}>{item[i].name.toUpperCase()}</Text>
              </View>
            </TouchableHighlight>
          )
        })}
      </View>
    )
  }

  render() {
    return (
      <View style={{ flex: 1 }}>
        <NavBarRegular
          title={"Ubione Mall"}
          iconLeft={<Icon name="menu" size={22} color="#FFF" />}
          iconLeftAction={() => { this.openDrawer() }}
          iconRight={<Icon name="shopping-cart" size={22} color="#FFF" />}
          iconRightAction={() => { this.openRequests() }}
        />
        {this.state.isLoading ?
          (<ActivityIndicator
            color={Colors.greyDark}
            size="large" />)
          :
          (
            <View style={[styles.mainContainer, styles.containerPadding]}>
              {!this.state.message ?
              (<FlatList
                data={this.state.mallsList}
                keyExtractor={this._keyExtractor}
                renderItem={this._renderItem}
              />):(
                <View style={{ flex: 1 ,marginTop:30}}><Text style={{alignSelf: 'center',color:  Colors.red,textAlign: 'center'}}>No hay resultados</Text></View>
              )}
            </View>
          )
        }
      </View>
    )
  }
}

const mapStateToProps = (state) => {
  return {
      user: state.login.user,
      mallUid: state.mall.mallUid
  }
}

const mapDispatchToProps = (dispatch) => {
  return {
    setMall: (uid) => dispatch(MallActions.mall(uid))
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(HomeScreen)